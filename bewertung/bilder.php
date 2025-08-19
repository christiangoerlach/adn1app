<?php
session_start(); // Session starten
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php'; // DB-Verbindung + .env geladen

// Hole Blob-Base-URL aus Umgebungsvariablen und Session
$blobBaseUrl = ($_ENV['BLOB_BASE_URL'] ?? '') . ($_SESSION['AZURE_STORAGE_CONTAINER_NAME'] ?? '') . '/';

// Projekt-ID aus Session holen oder Standard verwenden
$projektId = $_SESSION['PROJEKT_ID'] ?? 1; // Standard-Projekt-ID 1 verwenden

// Filter-Parameter aus URL holen
$filter = $_GET['filter'] ?? 'all';
$wert = $_GET['wert'] ?? null;

// SQL-Abfrage basierend auf Filter aufbauen
if ($filter === 'all') {
    // Alle Bilder des Projekts
    $sql = "SELECT [Id], [FileName] 
            FROM [dbo].[bilder]
            WHERE [projects-id] = :projekt_id
            ORDER BY [Id]";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':projekt_id', (int)$projektId, PDO::PARAM_INT);
    
} elseif ($filter === 'zustand' && $wert !== null) {
    // Bilder mit bestimmter Bewertung
    $sql = "SELECT b.[Id], b.[FileName] 
            FROM [dbo].[bilder] b
            INNER JOIN [dbo].[bewertung] bew ON b.[Id] = bew.[bilder-id]
            WHERE b.[projects-id] = :projekt_id AND bew.[strasse] = :wert
            ORDER BY b.[Id]";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':projekt_id', (int)$projektId, PDO::PARAM_INT);
    $stmt->bindValue(':wert', (int)$wert, PDO::PARAM_INT);
    
} elseif ($filter === 'nicht_bewertet') {
    // Bilder ohne Bewertung
    $sql = "SELECT b.[Id], b.[FileName] 
            FROM [dbo].[bilder] b
            LEFT JOIN [dbo].[bewertung] bew ON b.[Id] = bew.[bilder-id]
            WHERE b.[projects-id] = :projekt_id AND bew.[bilder-id] IS NULL
            ORDER BY b.[Id]";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':projekt_id', (int)$projektId, PDO::PARAM_INT);
    
} else {
    // Fallback: alle Bilder
    $sql = "SELECT [Id], [FileName] 
            FROM [dbo].[bilder]
            WHERE [projects-id] = :projekt_id
            ORDER BY [Id]";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':projekt_id', (int)$projektId, PDO::PARAM_INT);
}

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
