<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

try {
    // Aktuelle Projekt-ID aus der Session holen
    $projektId = $_SESSION['PROJEKT_ID'] ?? null;
    
    if ($projektId === null) {
        echo json_encode(['error' => 'Keine Projekt-ID in der Session gefunden']);
        exit;
    }
    
    // Projektnamen aus der Datenbank abrufen
    $sql = "SELECT Projektname FROM [dbo].[projects] WHERE Id = :projekt_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':projekt_id', (int)$projektId, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode(['projektname' => $result['Projektname']]);
    } else {
        echo json_encode(['error' => 'Projekt nicht gefunden']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
?>
