<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Prüfe ob POST-Daten vorhanden sind
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Nur POST-Requests erlaubt']);
    exit;
}

// Hole POST-Daten
$input = json_decode(file_get_contents('php://input'), true);
$bildId = $input['bildId'] ?? null;
$strasse = $input['strasse'] ?? null;

// Validiere Eingaben
if ($bildId === null || $strasse === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Bild-ID und Straßenbewertung sind erforderlich']);
    exit;
}

// Validiere Straßenbewertung (1-6 oder 0 für "noch nicht bewertet")
if (!in_array($strasse, [0, 1, 2, 3, 4, 5, 6])) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Straßenbewertung']);
    exit;
}

try {
    // Prüfe ob bereits eine Bewertung für dieses Bild existiert
    $checkSql = "SELECT Id FROM [dbo].[bewertung] WHERE [bilder-id] = :bild_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->fetch()) {
        // Update existierende Bewertung
        $sql = "UPDATE [dbo].[bewertung] 
                SET [strasse] = :strasse, [CreatedAt] = GETDATE() 
                WHERE [bilder-id] = :bild_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':strasse', (int)$strasse, PDO::PARAM_INT);
        $stmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'action' => 'updated']);
    } else {
        // Erstelle neue Bewertung
        $sql = "INSERT INTO [dbo].[bewertung] ([bilder-id], [strasse], [CreatedAt]) 
                VALUES (:bild_id, :strasse, GETDATE())";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
        $stmt->bindValue(':strasse', (int)$strasse, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'action' => 'created']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
?>
