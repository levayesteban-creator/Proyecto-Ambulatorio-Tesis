<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    private Client $http;
    private string $credentialsPath;
    private string $folderId;

    public function __construct()
    {
        $this->http = new Client(['base_uri' => 'https://www.googleapis.com']);
        $this->credentialsPath = storage_path('app/google-credentials.json');
        $this->folderId = '1n6nARyXJo584TQvGvm7_i8bo-hB2iraF';
    }

    public function upload(string $localPath, string $remoteName): bool
    {
        if (!file_exists($this->credentialsPath)) {
            Log::error('Google Drive: Credentials file not found at ' . $this->credentialsPath);
            return false;
        }

        if (!file_exists($localPath)) {
            Log::error('Google Drive: Local backup file not found at ' . $localPath);
            return false;
        }

        try {
            $token = $this->getAccessToken();
            $fileId = $this->uploadFile($token, $localPath, $remoteName);

            Log::info("Google Drive: Backup uploaded successfully. File ID: {$fileId}");
            return true;
        } catch (\Throwable $e) {
            Log::error('Google Drive upload failed: ' . $e->getMessage());
            return false;
        }
    }

    private function getAccessToken(): string
    {
        return Cache::remember('google_drive_token', 3500, function () {
            $scopes = ['https://www.googleapis.com/auth/drive.file'];
            $credentials = new ServiceAccountCredentials($scopes, $this->credentialsPath);
            $httpHandler = HttpHandlerFactory::build();
            $authToken = $credentials->fetchAuthToken($httpHandler);

            if (!isset($authToken['access_token'])) {
                throw new \RuntimeException('Failed to obtain Google Drive access token.');
            }

            return $authToken['access_token'];
        });
    }

    private function uploadFile(string $token, string $localPath, string $name): string
    {
        $metadata = json_encode([
            'name' => $name,
            'parents' => [$this->folderId],
        ]);

        $fileContent = file_get_contents($localPath);

        $boundary = 'boundary_' . uniqid();
        $bodyParts = [
            "--{$boundary}\r\nContent-Type: application/json; charset=UTF-8\r\n\r\n{$metadata}\r\n",
            "--{$boundary}\r\nContent-Type: application/octet-stream\r\n\r\n{$fileContent}\r\n",
            "--{$boundary}--",
        ];
        $body = implode('', $bodyParts);

        $resp = $this->http->post('upload/drive/v3/files?uploadType=multipart&supportsAllDrives=true', [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => "multipart/related; boundary={$boundary}",
            ],
            'body' => $body,
        ]);

        $data = json_decode((string) $resp->getBody(), true);
        return $data['id'];
    }
}
