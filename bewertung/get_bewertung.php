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
    // Hole Bewertung für das angegebene Bild (alle Felder)
    $sql = "SELECT [strasse], [gehweg_links], [gehweg_rechts], [seitenstreifen_links], [seitenstreifen_rechts], [review], [schaden], [text] 
            FROM [dbo].[bewertung] WHERE [bilder-id] = :bild_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'strasse' => (int)$result['strasse'],
            'gehweg_links' => $result['gehweg_links'] !== null ? (int)$result['gehweg_links'] : null,
            'gehweg_rechts' => $result['gehweg_rechts'] !== null ? (int)$result['gehweg_rechts'] : null,
            'seitenstreifen_links' => $result['seitenstreifen_links'] !== null ? (int)$result['seitenstreifen_links'] : null,
            'seitenstreifen_rechts' => $result['seitenstreifen_rechts'] !== null ? (int)$result['seitenstreifen_rechts'] : null,
            'review' => $result['review'] !== null ? (int)$result['review'] : 0,
            'schaden' => $result['schaden'] !== null ? (int)$result['schaden'] : 0,
            'notizen' => $result['text'] !== null ? $result['text'] : ''
        ]);
    } else {
        // Keine Bewertung gefunden
        echo json_encode([
            'strasse' => 0,
            'gehweg_links' => null,
            'gehweg_rechts' => null,
            'seitenstreifen_links' => null,
            'seitenstreifen_rechts' => null,
            'review' => 0,
            'schaden' => 0,
            'notizen' => ''
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
?>
