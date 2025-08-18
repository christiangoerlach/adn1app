<?php
session_start(); // Session starten
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php'; // DB-Verbindung + .env geladen

// Hole Blob-Base-URL aus Umgebungsvariablen und Session
$blobBaseUrl = ($_ENV['BLOB_BASE_URL'] ?? '') . ($_SESSION['AZURE_STORAGE_CONTAINER_NAME'] ?? '') . '/';

// Projekt-ID aus Session holen oder Standard verwenden
$projektId = $_SESSION['PROJEKT_ID'] ?? 1; // Standard-Projekt-ID 1 verwenden

// SQL-Abfrage mit WHERE und Platzhalter
$sql = "SELECT [Id], [FileName] 
        FROM [dbo].[bilder]
        WHERE [projects-id] = :projekt_id";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':projekt_id', (int)$projektId, PDO::PARAM_INT);
$stmt->execute();

$imageUrls = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fileName = $row['FileName'];
    $imageUrls[] = [
        'id' => $row['Id'],
        'url' => $blobBaseUrl . rawurlencode($fileName)
    ];
}

// JSON-Ausgabe
echo json_encode($imageUrls, JSON_UNESCAPED_SLASHES);
?>
