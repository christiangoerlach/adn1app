<?php
require 'vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


// Azure Storage Connection String
$connectionString = $_ENV['AZURE_STORAGE_CONNECTION_STRING'];
$containerName = $_ENV['AZURE_STORAGE_CONTAINER_NAME'];


$blobClient = BlobRestProxy::createBlobService($connectionString);

try {
    // Liste aller Blobs (Bilder) im Container
    $blobList = $blobClient->listBlobs($containerName);
    $blobs = $blobList->getBlobs();

    // Bilder-URLs sammeln (Shared Access Signature oder public URL vorausgesetzt)
    $images = [];
    foreach ($blobs as $blob) {


        $blobName = $blob->getName();
        // URL zum Blob (Anpassen, falls du SAS-Token brauchst)
        $url = "https://cgml15199447121.blob.core.windows.net/$containerName/$blobName";

        $images[] = $url;
    }

} catch(ServiceException $e){
    echo "Error: " . $e->getMessage();
    die();
}

// Index des aktuell angezeigten Bildes, via GET-Parameter steuern
$currentIndex = isset($_GET['index']) ? (int)$_GET['index'] : 0;
if ($currentIndex < 0) $currentIndex = 0;
if ($currentIndex >= count($images)) $currentIndex = count($images) - 1;

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>Bilder aus Azure Blob mit Umgebungsvariablen</title>
    <style>
        body { text-align: center; font-family: Arial, sans-serif; }
        img { max-width: 80vw; max-height: 80vh; margin: 20px auto; display: block; }
        .nav-buttons { margin: 20px; }
        button { font-size: 1.2rem; padding: 10px 20px; margin: 0 10px; }
    </style>
</head>
<body>

<h1>21:34 Bilder aus Azure Blob Storage</h1>

<?php if (count($images) > 0): ?>
    <img src="<?= htmlspecialchars($images[$currentIndex]) ?>" alt="Bild <?= $currentIndex + 1 ?>" />
    
    <div class="nav-buttons">
        <form method="get" style="display: inline;">
            <input type="hidden" name="index" value="<?= max(0, $currentIndex - 1) ?>" />
            <button type="submit" <?= $currentIndex === 0 ? 'disabled' : '' ?>>← Zurück</button>
        </form>

        <span><?= ($currentIndex + 1) ?> / <?= count($images) ?></span>

        <form method="get" style="display: inline;">
            <input type="hidden" name="index" value="<?= min(count($images) - 1, $currentIndex + 1) ?>" />
            <button type="submit" <?= $currentIndex === count($images) - 1 ? 'disabled' : '' ?>>Vor →</button>
        </form>
    </div>

<?php else: ?>
    <p>Keine Bilder im Blob Storage gefunden.</p>
<?php endif; ?>

</body>
</html>
