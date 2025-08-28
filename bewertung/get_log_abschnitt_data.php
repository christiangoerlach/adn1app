<?php
session_start();
header('Content-Type: application/json');

// Prüfen ob ein Projekt ausgewählt ist
if (!isset($_SESSION['PROJEKT_ID'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Kein Projekt ausgewählt']);
    exit;
}

// Parameter holen
$abschnittId = $_GET['abschnittId'] ?? null;

if (!$abschnittId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'AbschnittId ist erforderlich']);
    exit;
}

// Datenbankverbindung
require_once '../config/database.php';

try {
    $sql = "SELECT [Zeitstempel], [Feld], [Wert], [Nutzer] 
            FROM [dbo].[log_abschnitte_bewertung] 
            WHERE [abschnitte_id] = ? 
            ORDER BY [Zeitstempel] DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$abschnittId]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'logs' => $logs]);
    
} catch (PDOException $e) {
    error_log('Fehler beim Laden der Log-Daten: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Datenbankfehler']);
}
?>
