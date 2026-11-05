<?php

namespace Tests\Unit;

use App\Models\DatabaseBackup;
use App\Services\DatabaseBackupExcelService;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class DatabaseBackupExcelServiceTest extends TestCase
{
    public function test_it_exports_the_selected_sql_snapshot_to_excel(): void
    {
        Storage::fake('local');

        $sql = <<<'SQL'
CREATE TABLE `users` (
  `id` bigint NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB;
INSERT INTO `users` VALUES (1,'Ana\'s\nname','=2+2'),(2,NULL,'00123');
CREATE TABLE `empty_table` (
  `id` bigint NOT NULL
) ENGINE=InnoDB;
SQL;

        Storage::disk('local')->put('database-backups/snapshot.sql', $sql);
        $backup = new DatabaseBackup([
            'path' => 'database-backups/snapshot.sql',
            'filename' => 'snapshot.sql',
        ]);

        $path = app(DatabaseBackupExcelService::class)->export($backup);
        $book = IOFactory::load(Storage::disk('local')->path($path));

        $this->assertSame('database-backups/snapshot.xlsx', $path);
        $this->assertSame(['users', 'empty_table'], $book->getSheetNames());
        $this->assertSame('Ana\'s' . "\n" . 'name', $book->getSheetByName('users')->getCell('B2')->getValue());
        $this->assertSame('=2+2', $book->getSheetByName('users')->getCell('C2')->getValue());
        $this->assertSame('s', $book->getSheetByName('users')->getCell('C2')->getDataType());
        $this->assertSame('00123', $book->getSheetByName('users')->getCell('C3')->getValue());
        $this->assertSame('id', $book->getSheetByName('empty_table')->getCell('A1')->getValue());
        $book->disconnectWorksheets();
    }
}
