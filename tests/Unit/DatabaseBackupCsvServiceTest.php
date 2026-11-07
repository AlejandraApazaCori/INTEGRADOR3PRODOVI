<?php

namespace Tests\Unit;

use App\Models\DatabaseBackup;
use App\Services\DatabaseBackupCsvService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class DatabaseBackupCsvServiceTest extends TestCase
{
    public function test_it_exports_each_table_from_the_selected_sql_backup_as_csv(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('database-backups/snapshot.sql', <<<'SQL'
CREATE TABLE `users` (
  `id` bigint NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB;
INSERT INTO `users` VALUES (1,'Ana\'s','=2+2'),(2,NULL,'00123');
CREATE TABLE `empty_table` (
  `id` bigint NOT NULL
) ENGINE=InnoDB;
SQL);

        $backup = new DatabaseBackup(['path' => 'database-backups/snapshot.sql', 'filename' => 'snapshot.sql']);
        $path = app(DatabaseBackupCsvService::class)->export($backup);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open(Storage::disk('local')->path($path)) === true);
        $this->assertSame('database-backups/snapshot-csv.zip', $path);
        $this->assertSame(['001-users.csv', '002-empty_table.csv'], [$zip->getNameIndex(0), $zip->getNameIndex(1)]);

        $stream = $zip->getStream('001-users.csv');
        $headings = fgetcsv($stream);
        $headings[0] = substr($headings[0], 3);
        $this->assertSame(['id', 'name', 'code'], $headings);
        $this->assertSame(['1', "Ana's", "'=2+2"], fgetcsv($stream));
        $this->assertSame(['2', '', '00123'], fgetcsv($stream));
        fclose($stream);
        $this->assertStringContainsString('id', $zip->getFromName('002-empty_table.csv'));
        $zip->close();
    }
}
