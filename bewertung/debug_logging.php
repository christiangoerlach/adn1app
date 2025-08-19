<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Debug-Ausgaben
error_log("Debug: save_bewertung.php aufgerufen");
error_log("POST-Daten: " . print_r($_POST, true));
error_log("JSON-Body: " . file_get_contents('php://input'));

// Simuliere einen POST-Request
$_POST = [
    'bildId' => 1,
    'strasse' => 5,
];

$bildId = $_POST['bildId'] ?? null;
$strasse = $_POST['strasse'] ?? null;

error_log("Bild-ID: $bildId, Straße: $strasse");

try {
    // Prüfe ob bereits eine Bewertung für dieses Bild existiert
    $checkSql = "SELECT Id FROM [dbo].[bewertung] WHERE [bilder-id] = :bild_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->fetch()) {
        error_log("Update existierende Bewertung");
        // Update existierende Bewertung
        $sql = "UPDATE [dbo].[bewertung] 
                SET [strasse] = :strasse
                WHERE [bilder-id] = :bild_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':strasse', (int)$strasse, PDO::PARAM_INT);
        $stmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Log-Eintrag für Update erstellen
        createLogEntry($conn, $bildId, 'strasse', $strasse, 'update');
        
        echo json_encode(['success' => true, 'action' => 'updated']);
    } else {
        error_log("Erstelle neue Bewertung");
        // Erstelle neue Bewertung
        $sql = "INSERT INTO [dbo].[bewertung] ([bilder-id], [strasse]) 
                VALUES (:bild_id, :strasse)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
        $stmt->bindValue(':strasse', (int)$strasse, PDO::PARAM_INT);
        $stmt->execute();
        
        // Log-Eintrag für neue Bewertung erstellen
        createLogEntry($conn, $bildId, 'strasse', $strasse, 'create');
        
        echo json_encode(['success' => true, 'action' => 'created']);
    }
    
} catch (PDOException $e) {
    error_log("Datenbankfehler: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}

/**
 * Erstellt einen Log-Eintrag in der log_bewertung Tabelle
 */
function createLogEntry($conn, $bildId, $feld, $wert, $aktion) {
    try {
        error_log("createLogEntry aufgerufen: Bild-ID $bildId, Feld $feld, Wert $wert, Aktion $aktion");
        
        // Aktueller Benutzer aus der Session (falls verfügbar)
        $nutzer = $_SESSION['USER_NAME'] ?? $_SESSION['USER_ID'] ?? 'unbekannt';
        error_log("Benutzer: $nutzer");
        
        // Log-Eintrag erstellen
        $sql = "INSERT INTO [dbo].[log_bewertung] ([bilder_id], [Feld], [Wert], [Nutzer]) 
                VALUES (:bilder_id, :feld, :wert, :nutzer)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':bilder_id', (int)$bildId, PDO::PARAM_INT);
        $stmt->bindValue(':feld', $feld, PDO::PARAM_STR);
        $stmt->bindValue(':wert', (int)$wert, PDO::PARAM_INT);
        $stmt->bindValue(':nutzer', $nutzer, PDO::PARAM_STR);
        $stmt->execute();
        
        // Log-Eintrag erfolgreich erstellt
        error_log("Log-Eintrag erfolgreich erstellt in der Datenbank");
        
    } catch (PDOException $e) {
        error_log("Fehler beim Erstellen des Log-Eintrags: " . $e->getMessage());
    }
}
?>
