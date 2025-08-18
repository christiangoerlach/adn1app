<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Prüfe ob GET-Parameter vorhanden sind
$bildId = $_GET['bildId'] ?? null;

if ($bildId === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Bild-ID ist erforderlich']);
    exit;
}

try {
    // Hole Bewertung für das angegebene Bild
    $sql = "SELECT [strasse] FROM [dbo].[bewertung] WHERE [bilder-id] = :bild_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode(['strasse' => (int)$result['strasse']]);
    } else {
        // Keine Bewertung gefunden
        echo json_encode(['strasse' => 0]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
?>
