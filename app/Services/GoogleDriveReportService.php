<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\EmbeddedObjectPosition;
use Google\Service\Sheets\GridCoordinate;
use Google\Service\Sheets\GridProperties;
use Google\Service\Sheets\OverlayPosition;
use Google\Service\Sheets\Request as SheetsRequest;
use Google\Service\Sheets\SheetProperties;
use Google\Service\Sheets\UpdateEmbeddedObjectPositionRequest;
use Google\Service\Sheets\UpdateSheetPropertiesRequest;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GoogleDriveReportService
{
    private Drive $drive;

    private GoogleSheets $sheets;

    public function __construct()
    {
        $clientId = config('google-drive.client_id');
        $clientSecret = config('google-drive.client_secret');
        $refreshToken = config('google-drive.refresh_token');
        $accessToken = config('google-drive.access_token');

        if (!$clientId || !$clientSecret || (!$refreshToken && !$accessToken)) {
            throw new RuntimeException('La configuración OAuth de Google Drive está incompleta.');
        }

        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri(config('google-drive.redirect_uri'));
        $client->setScopes([Drive::DRIVE]);
        $client->setAccessType('offline');
        $verifySsl = app()->environment('local')
            ? false
            : (bool) config('google-drive.verify_ssl', true);

        $client->setHttpClient(new HttpClient([
            'verify' => $verifySsl,
            'timeout' => 30,
        ]));

        if ($refreshToken) {
            try {
                $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
                if (isset($token['error'])) {
                    throw new RuntimeException($token['error_description'] ?? $token['error']);
                }
                $client->setAccessToken($token);
            } catch (\Throwable $exception) {
                if (!$accessToken) {
                    throw $exception;
                }
                Log::warning('No se pudo renovar el token de Google Drive; se usará el access token configurado.', [
                    'error' => $exception->getMessage(),
                ]);
                $client->setAccessToken(['access_token' => $accessToken]);
            }
        } else {
            $client->setAccessToken(['access_token' => $accessToken]);
        }

        $this->drive = new Drive($client);
        $this->sheets = new GoogleSheets($client);
    }

    public function uploadAsGoogleSheet(string $fileName, string $contents): array
    {
        $folderId = $this->ensureTargetFolder();
        return $this->saveGoogleSheet($fileName, $contents, $folderId);
    }

    public function saveGoogleSheet(string $fileName, string $contents, string $folderId, ?string $existingFileId = null): array
    {
        $sheetName = pathinfo($fileName, PATHINFO_FILENAME);

        if ($existingFileId) {
            try {
                $current = $this->drive->files->get($existingFileId, ['fields' => 'id,name,parents,trashed']);
                if (!$current->getTrashed()) {
                    $currentParents = $current->getParents() ?: [];
                    $options = [
                        'data' => $contents,
                        'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'uploadType' => 'multipart',
                        'fields' => 'id,name,webViewLink,parents',
                    ];
                    if (!in_array($folderId, $currentParents, true)) {
                        $options['addParents'] = $folderId;
                        if ($currentParents !== []) {
                            $options['removeParents'] = implode(',', $currentParents);
                        }
                    }

                    $file = $this->drive->files->update($existingFileId, new DriveFile(['name' => $sheetName]), $options);

                    return $this->fileResult($file);
                }
            } catch (\Google\Service\Exception $exception) {
                if ($exception->getCode() !== 404) {
                    throw $exception;
                }
            }
        }

        $metadata = new DriveFile([
            'name' => $sheetName,
            'mimeType' => 'application/vnd.google-apps.spreadsheet',
            'parents' => [$folderId],
        ]);

        $file = $this->drive->files->create($metadata, [
            'data' => $contents,
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'uploadType' => 'multipart',
            'fields' => 'id,name,webViewLink',
        ]);

        return $this->fileResult($file);
    }

    public function saveGoogleDoc(string $fileName, string $html, string $folderId, ?string $existingFileId = null): array
    {
        $options = [
            'data' => $html,
            'mimeType' => 'text/html',
            'uploadType' => 'multipart',
            'fields' => 'id,name,webViewLink,parents',
        ];

        if ($existingFileId) {
            try {
                $current = $this->drive->files->get($existingFileId, ['fields' => 'id,parents,trashed']);
                if (!$current->getTrashed()) {
                    $parents = $current->getParents() ?: [];
                    if (!in_array($folderId, $parents, true)) {
                        $options['addParents'] = $folderId;
                        if ($parents !== []) {
                            $options['removeParents'] = implode(',', $parents);
                        }
                    }
                    $file = $this->drive->files->update($existingFileId, new DriveFile(['name' => $fileName]), $options);

                    return $this->fileResult($file, 'document');
                }
            } catch (\Google\Service\Exception $exception) {
                if ($exception->getCode() !== 404) {
                    throw $exception;
                }
            }
        }

        $file = $this->drive->files->create(new DriveFile([
            'name' => $fileName,
            'mimeType' => 'application/vnd.google-apps.document',
            'parents' => [$folderId],
        ]), $options);

        return $this->fileResult($file, 'document');
    }

    public function saveDocxAsGoogleDoc(string $fileName, string $contents, string $folderId, ?string $existingFileId = null): array
    {
        $options = [
            'data' => $contents,
            'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'uploadType' => 'multipart',
            'fields' => 'id,name,webViewLink,parents',
        ];

        if ($existingFileId) {
            try {
                $current = $this->drive->files->get($existingFileId, ['fields' => 'id,parents,trashed']);
                if (!$current->getTrashed()) {
                    $parents = $current->getParents() ?: [];
                    if (!in_array($folderId, $parents, true)) {
                        $options['addParents'] = $folderId;
                        if ($parents !== []) $options['removeParents'] = implode(',', $parents);
                    }
                    $file = $this->drive->files->update($existingFileId, new DriveFile(['name' => $fileName]), $options);
                    return $this->fileResult($file, 'document');
                }
            } catch (\Google\Service\Exception $exception) {
                if ($exception->getCode() !== 404) throw $exception;
            }
        }

        $file = $this->drive->files->create(new DriveFile([
            'name' => $fileName,
            'mimeType' => 'application/vnd.google-apps.document',
            'parents' => [$folderId],
        ]), $options);

        return $this->fileResult($file, 'document');
    }

    public function companyDocumentFolders(string $companyName): array
    {
        $rootId = $this->ensureCompanyFolder($companyName);
        return [
            'root' => ['id' => $rootId, 'name' => $companyName],
            'folders' => $this->childFolders($rootId),
        ];
    }

    public function resolveCompanyDocumentFolder(string $companyName, ?string $folderId, ?string $newFolderName): string
    {
        $rootId = $this->ensureCompanyFolder($companyName);
        $newFolderName = trim((string) $newFolderName);
        if ($newFolderName !== '') return $this->findOrCreateFolder($newFolderName, $rootId);
        if (!$folderId || $folderId === $rootId) return $rootId;

        $folder = $this->drive->files->get($folderId, ['fields' => 'id,mimeType,parents,trashed']);
        if ($folder->getTrashed() || $folder->getMimeType() !== 'application/vnd.google-apps.folder' || !in_array($rootId, $folder->getParents() ?: [], true)) {
            throw new RuntimeException('La carpeta seleccionada no pertenece a la empresa.');
        }
        return $folderId;
    }

    private function ensureCompanyFolder(string $companyName): string
    {
        $companiesId = $this->findOrCreateFolder('Empresas', $this->ensureTargetFolder());
        return $this->findOrCreateFolder(trim($companyName), $companiesId);
    }

    private function childFolders(string $parentId): array
    {
        $escaped = str_replace("'", "\\'", $parentId);
        $folders = $this->drive->files->listFiles([
            'q' => "'{$escaped}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'spaces' => 'drive', 'orderBy' => 'name', 'fields' => 'files(id,name)', 'pageSize' => 100,
        ])->getFiles();
        return array_map(fn (DriveFile $folder) => ['id' => $folder->getId(), 'name' => $folder->getName()], $folders);
    }

    public function positionUserReportCharts(string $spreadsheetId): void
    {
        $this->positionReportCharts($spreadsheetId, 'Usuarios', 'estado');
    }

    public function positionPaymentReportCharts(string $spreadsheetId): void
    {
        $this->positionReportCharts($spreadsheetId, 'Pagos', 'método');
    }

    private function positionReportCharts(string $spreadsheetId, string $worksheetTitle, string $rightChartKeyword): void
    {
        $worksheet = null;
        usleep(750000);

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $spreadsheet = $this->sheets->spreadsheets->get($spreadsheetId, [
                'fields' => 'sheets(properties(sheetId,title),charts(chartId,spec(title)))',
            ]);
            $worksheets = $spreadsheet->getSheets();
            $worksheet = collect($worksheets)->first(fn ($sheet) => $sheet->getProperties()->getTitle() === $worksheetTitle)
                ?? ($worksheets[0] ?? null);

            if ($worksheet && count($worksheet->getCharts() ?? []) >= 2) {
                break;
            }

            usleep(500000);
        }

        if (!$worksheet || count($worksheet->getCharts() ?? []) < 2) {
            throw new RuntimeException('Google Sheets no terminó de importar las gráficas del reporte.');
        }

        $sheetId = $worksheet->getProperties()->getSheetId();
        $gridProperties = new GridProperties([
            'frozenRowCount' => 0,
            'frozenColumnCount' => 0,
        ]);
        $sheetProperties = new SheetProperties([
            'sheetId' => $sheetId,
            'gridProperties' => $gridProperties,
        ]);
        $unfreezeSheet = new UpdateSheetPropertiesRequest([
            'properties' => $sheetProperties,
            'fields' => 'gridProperties.frozenRowCount,gridProperties.frozenColumnCount',
        ]);
        $requests = [new SheetsRequest(['updateSheetProperties' => $unfreezeSheet])];

        foreach ($worksheet->getCharts() as $index => $chart) {
            $title = mb_strtolower((string) $chart->getSpec()?->getTitle());
            $columnIndex = str_contains($title, $rightChartKeyword) ? 4 : ($index === 0 ? 0 : 4);
            $anchor = new GridCoordinate([
                'sheetId' => $sheetId,
                'rowIndex' => 3,
                'columnIndex' => $columnIndex,
            ]);
            $overlay = new OverlayPosition([
                'anchorCell' => $anchor,
                'offsetXPixels' => 0,
                'offsetYPixels' => 0,
                'widthPixels' => 500,
                'heightPixels' => 280,
            ]);
            $position = new EmbeddedObjectPosition(['overlayPosition' => $overlay]);
            $update = new UpdateEmbeddedObjectPositionRequest([
                'objectId' => $chart->getChartId(),
                'newPosition' => $position,
                'fields' => '*',
            ]);
            $requests[] = new SheetsRequest(['updateEmbeddedObjectPosition' => $update]);
        }

        $this->sheets->spreadsheets->batchUpdate(
            $spreadsheetId,
            new BatchUpdateSpreadsheetRequest(['requests' => $requests]),
        );
    }

    public function listTargetFolders(): array
    {
        $rootId = $this->ensureTargetFolder();
        $escapedRoot = str_replace("'", "\\'", $rootId);
        $folders = $this->drive->files->listFiles([
            'q' => "'{$escapedRoot}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'spaces' => 'drive',
            'orderBy' => 'name',
            'fields' => 'files(id,name)',
            'pageSize' => 100,
        ])->getFiles();

        return [
            'root' => ['id' => $rootId, 'name' => config('google-drive.folder', 'PRODOVI')],
            'folders' => array_map(fn (DriveFile $folder) => [
                'id' => $folder->getId(),
                'name' => $folder->getName(),
            ], $folders),
        ];
    }

    public function resolveTargetFolder(?string $folderId, ?string $newFolderName): string
    {
        $rootId = $this->ensureTargetFolder();
        $newFolderName = trim((string) $newFolderName);

        if ($newFolderName !== '') {
            return $this->findOrCreateFolder($newFolderName, $rootId);
        }

        if (!$folderId || $folderId === $rootId) {
            return $rootId;
        }

        $folder = $this->drive->files->get($folderId, ['fields' => 'id,mimeType,parents,trashed']);
        if ($folder->getTrashed()
            || $folder->getMimeType() !== 'application/vnd.google-apps.folder'
            || !in_array($rootId, $folder->getParents() ?: [], true)) {
            throw new RuntimeException('La carpeta seleccionada no pertenece a PRODOVI.');
        }

        return $folderId;
    }

    private function fileResult(DriveFile $file, string $type = 'spreadsheet'): array
    {
        return [
            'id' => $file->getId(),
            'name' => $file->getName(),
            'url' => $file->getWebViewLink() ?: 'https://docs.google.com/'.($type === 'document' ? 'document' : 'spreadsheets').'/d/' . $file->getId() . '/edit',
        ];
    }

    public function ensureTargetFolder(): string
    {
        return config('google-drive.folder_id')
            ?: $this->findOrCreateFolder(config('google-drive.folder', 'PRODOVI'));
    }

    private function findOrCreateFolder(string $folderName, string $parentId = 'root'): string
    {
        $escapedName = str_replace(["\\", "'"], ["\\\\", "\\'"], $folderName);
        $escapedParent = str_replace("'", "\\'", $parentId);
        $folders = $this->drive->files->listFiles([
            'q' => "name='{$escapedName}' and mimeType='application/vnd.google-apps.folder' and trashed=false and '{$escapedParent}' in parents",
            'spaces' => 'drive',
            'fields' => 'files(id,name)',
            'pageSize' => 1,
        ])->getFiles();

        if ($folders !== []) {
            return $folders[0]->getId();
        }

        $folder = $this->drive->files->create(new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId],
        ]), ['fields' => 'id']);

        return $folder->getId();
    }
}
