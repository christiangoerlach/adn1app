
0750
<?php
header('Content-Type: application/json');

// Verbindung zur Datenbank herstellen
require_once 'db.php';

// SQL nur die benötigte Spalte holen
$sql = "SELECT [FileName] FROM [dbo].[ImageRegistry]";
$stmt = $conn->query($sql);

// Array für URLs vorbereiten
$imageUrls = [];

// Azure Blob Storage Basis-URL (bitte anpassen)
$blobBaseUrl = "https://<dein-storage-account>.blob.core.windows.net/<dein-container>/";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fileName = $row['FileName'];

    // URL zusammensetzen
    $imageUrls[] = $blobBaseUrl . rawurlencode($fileName);
}

// JSON ausgeben
echo json_encode($imageUrls);
?>
