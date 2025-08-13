<?php
header('Content-Type: application/json');

require_once 'db.php'; // DB-Verbindung + .env geladen

// Hole Blob-Base-URL aus Umgebungsvariablen
$blobBaseUrl = $_ENV['BLOB_BASE_URL'] ?? '';

// SQL-Abfrage
$sql = "SELECT [FileName] FROM [dbo].[ImageRegistry]";
$stmt = $conn->query($sql);

$imageUrls = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fileName = $row['FileName'];
    $imageUrls[] = $blobBaseUrl . rawurlencode($fileName);
}

// JSON-Ausgabe
echo json_encode($imageUrls, JSON_UNESCAPED_SLASHES);
?>
