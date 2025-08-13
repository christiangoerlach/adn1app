<?php
require 'vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$connectionString = $_ENV['AZURE_STORAGE_CONNECTION_STRING'];
$containerName = $_ENV['AZURE_STORAGE_CONTAINER_NAME'];

$blobClient = BlobRestProxy::createBlobService($connectionString);

try {
    $blobList = $blobClient->listBlobs($containerName);
    $blobs = $blobList->getBlobs();

    $images = [];
    foreach ($blobs as $blob) {
        $blobName = $blob->getName();
        $url = "https://cgml15199447121.blob.core.windows.net/$containerName/$blobName";
        $images[] = $url;
    }

    header('Content-Type: application/json');
    echo json_encode($images);

} catch(ServiceException $e){
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
