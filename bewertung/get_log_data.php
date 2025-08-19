<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Prüfe ob Bild-ID übergeben wurde
$bildId = $_GET['bildId'] ?? null;

if ($bildId === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Bild-ID ist erforderlich']);
    exit;
}

try {
    // Log-Daten für das angegebene Bild abrufen
    $sql = "SELECT [CreatedAt], [Nutzer], [Feld], [Wert] 
            FROM [dbo].[log_bewertung] 
            WHERE [bilder_id] = :bild_id 
            ORDER BY [CreatedAt] DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
    $stmt->execute();
    
    $logData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($logData);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
?>
