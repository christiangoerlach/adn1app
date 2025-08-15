<?php
session_start(); // Session starten
header('Content-Type: application/json');

require_once '../db.php'; // DB-Verbindung + .env geladen

// Hole Blob-Base-URL aus Umgebungsvariablen und Session
$blobBaseUrl = ($_ENV['BLOB_BASE_URL'] ?? '') . ($_SESSION['AZURE_STORAGE_CONTAINER_NAME'] ?? '') . '/';

// Projekt-ID aus Session holen
$projektId = $_SESSION['PROJEKT_ID'] ?? null;

if ($projektId === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Keine PROJEKT_ID in der Session gefunden']);
    exit;
}

// SQL-Abfrage mit WHERE und Platzhalter
$sql = "SELECT [FileName] 
        FROM [dbo].[bilder]
        WHERE [projects-id] = :projekt_id";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':projekt_id', (int)$projektId, PDO::PARAM_INT);
$stmt->execute();

$imageUrls = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fileName = $row['FileName'];
    $imageUrls[] = $blobBaseUrl . rawurlencode($fileName);
}

// JSON-Ausgabe
echo json_encode($imageUrls, JSON_UNESCAPED_SLASHES);
