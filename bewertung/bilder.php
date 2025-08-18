<?php
session_start(); // Session starten
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php'; // DB-Verbindung + .env geladen

// Hole Blob-Base-URL aus Umgebungsvariablen und Session
$blobBaseUrl = ($_ENV['BLOB_BASE_URL'] ?? '') . ($_SESSION['AZURE_STORAGE_CONTAINER_NAME'] ?? '');
// Entferne doppelte Schrägstriche
$blobBaseUrl = rtrim($blobBaseUrl, '/') . '/';

// SQL-Abfrage
$sql = "SELECT [FileName] FROM [dbo].[bilder]";
$stmt = $conn->query($sql);

$imageUrls = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fileName = $row['FileName'];
    $imageUrls[] = $blobBaseUrl . rawurlencode($fileName);
}

// JSON-Ausgabe
echo json_encode($imageUrls, JSON_UNESCAPED_SLASHES);
?>
