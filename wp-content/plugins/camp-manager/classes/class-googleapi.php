<?php

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class CampManagerGoogleAPI {
    private $credentialsPath;
    private $tokenPath;
    private $folderId;

    public function __construct($credentialsPath, $tokenPath, $folderId) {
        $this->credentialsPath = $credentialsPath;
        $this->tokenPath = $tokenPath;
        $this->folderId = $folderId;
    }

    private function getClient() {
        $client = new Client();
        $client->setAuthConfig($this->credentialsPath);
        $client->addScope(Drive::DRIVE_FILE);
        $client->setAccessType('offline');

        if (file_exists($this->tokenPath)) {
            $accessToken = json_decode(file_get_contents($this->tokenPath), true);
            $client->setAccessToken($accessToken);
        } else {
            throw new \RuntimeException("Missing access token file. Run auth flow.");
        }

        if ($client->isAccessTokenExpired()) {
            throw new \RuntimeException("Google API token expired. Refresh or reauthorize.");
        }

        return $client;
    }

    public function uploadFile($filepath, $filename) {
        $client = $this->getClient();
        $service = new Drive($client);

        $fileMetadata = new DriveFile([
            'name' => $filename,
            'parents' => [$this->folderId]
        ]);

        $content = file_get_contents($filepath);

        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => mime_content_type($filepath),
            'uploadType' => 'multipart',
            'fields' => 'id, webViewLink',
        ]);

        return $file;
    }
}
