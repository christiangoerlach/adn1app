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

// Hole POST-Daten (robust: JSON-Body oder Form-POST)
$rawBody = file_get_contents('php://input');
$input = [];

// Erst JSON versuchen
if ($rawBody !== false && strlen($rawBody) > 0) {
    $decoded = json_decode($rawBody, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $input = $decoded;
    }
}

// Fallback: x-www-form-urlencoded oder multipart/form-data
if (empty($input) && !empty($_POST)) {
    $input = $_POST;
}

$bildId = $input['bildId'] ?? null;
$feld = $input['feld'] ?? null;
$wert = $input['wert'] ?? null;

// Validiere Eingaben
if ($bildId === null || $feld === null || $wert === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Bild-ID, Feld und Wert sind erforderlich']);
    exit;
}

// Validiere Feldnamen
$erlaubteFelder = ['review', 'schaden'];
if (!in_array($feld, $erlaubteFelder)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültiges Feld']);
    exit;
}

// Validiere Werte (0 oder 1)
if (!in_array($wert, [0, 1])) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültiger Wert - nur 0 oder 1 erlaubt']);
    exit;
}

try {
    // Prüfe ob bereits eine Bewertung für dieses Bild existiert
    $checkSql = "SELECT Id, [$feld] FROM [dbo].[bewertung] WHERE [bilder-id] = :bild_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
    $checkStmt->execute();
    
    $existingBewertung = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingBewertung) {
        // Prüfe ob sich der Wert tatsächlich geändert hat
        $oldValue = $existingBewertung[$feld];
        
        if ($oldValue != $wert) {
            // Update existierende Bewertung nur wenn sich der Wert geändert hat
            $sql = "UPDATE [dbo].[bewertung] 
                    SET [$feld] = :wert
                    WHERE [bilder-id] = :bild_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':wert', (int)$wert, PDO::PARAM_INT);
            $stmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Log-Eintrag für Update erstellen
            createLogEntry($conn, $bildId, $feld, $wert, 'update');
            
            echo json_encode(['success' => true, 'action' => 'updated']);
        } else {
            // Keine Änderung - kein Update und kein Log
            echo json_encode(['success' => true, 'action' => 'no_change']);
        }
    } else {
        // Erstelle neue Bewertung
        $sql = "INSERT INTO [dbo].[bewertung] ([bilder-id], [$feld]) 
                VALUES (:bild_id, :wert)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
        $stmt->bindValue(':wert', (int)$wert, PDO::PARAM_INT);
        $stmt->execute();
        
        // Log-Eintrag für neue Bewertung erstellen
        createLogEntry($conn, $bildId, $feld, $wert, 'create');
        
        echo json_encode(['success' => true, 'action' => 'created']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}

/**
 * Erstellt einen Log-Eintrag in der log_bewertung Tabelle
 */
function createLogEntry($conn, $bildId, $feld, $wert, $aktion) {
    try {
        // Aktueller Benutzer aus der Session (falls verfügbar)
        $nutzer = $_SESSION['USER_NAME'] ?? $_SESSION['USER_ID'] ?? 'unbekannt';
        
        // Versuche Benutzer aus verschiedenen Quellen zu holen
        if ($nutzer === 'unbekannt') {
            if (!empty($_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"])) {
                $useremail = $_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"];
                $nutzer = explode('@', $useremail)[0];
                $nutzer = ucwords(str_replace('.', ' ', $nutzer));
            } elseif (isset($_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL'])) {
                $principal = json_decode(base64_decode($_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL']), true);
                if (isset($principal['name'])) {
                    $nutzer = $principal['name'];
                }
            } elseif (isset($_SERVER['LOGON_USER'])) {
                $nutzer = $_SERVER['LOGON_USER'];
            }
        }
        
        // Log-Eintrag erstellen
        $sql = "INSERT INTO [dbo].[log_bewertung] ([bilder_id], [Feld], [Wert], [Nutzer], [Zeitstempel]) 
                VALUES (:bilder_id, :feld, :wert, :nutzer, :zeitstempel)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':bilder_id', (int)$bildId, PDO::PARAM_INT);
        $stmt->bindValue(':feld', $feld, PDO::PARAM_STR);
        $stmt->bindValue(':wert', (int)$wert, PDO::PARAM_INT);
        $stmt->bindValue(':nutzer', $nutzer, PDO::PARAM_STR);
        
        // Aktuelle Zeit in CET (Central European Time)
        $timezone = new DateTimeZone('Europe/Berlin');
        $datetime = new DateTime('now', $timezone);
        $stmt->bindValue(':zeitstempel', $datetime->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        
        $stmt->execute();
        
    } catch (PDOException $e) {
        error_log("Fehler beim Erstellen des Log-Eintrags: " . $e->getMessage());
    }
}
?>
