<?php
session_start();
header('Content-Type: application/json');

// Prüfen ob ein Projekt ausgewählt ist
if (!isset($_SESSION['PROJEKT_ID'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Kein Projekt ausgewählt']);
    exit;
}

// Prüfen ob POST-Request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Nur POST-Requests erlaubt']);
    exit;
}

// Parameter aus POST holen
$abschnittId = $_POST['abschnittId'] ?? null;
$field = $_POST['field'] ?? null;
$value = $_POST['value'] ?? null;

// Validierung der Parameter
if (!$abschnittId || !$field || $value === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Alle Parameter müssen angegeben werden']);
    exit;
}

// Datenbankverbindung
require_once '../config/database.php';

try {
    // Erlaubte Felder definieren
    $erlaubteFelder = ['strasse', 'gehweg_links', 'gehweg_rechts', 'seitenstreifen_links', 'seitenstreifen_rechts', 'review', 'schaden', 'text'];
    
    if (!in_array($field, $erlaubteFelder)) {
        throw new Exception('Ungültiges Feld: ' . $field);
    }
    
    // Validierung je nach Feld
    if ($field === 'text') {
        // Für Text-Feld: Länge prüfen (angenommen max 1000 Zeichen)
        if (strlen($value) > 1000) {
            throw new Exception('Text zu lang (max. 1000 Zeichen)');
        }
    } else {
        // Für numerische Felder: Erlaubte Werte definieren
        $erlaubteWerte = [0, 1, 2, 3, 4, 5, 6, 9, 10, 11];
        
        if (!in_array((int)$value, $erlaubteWerte)) {
            throw new Exception('Ungültiger Wert: ' . $value);
        }
    }
    
    // Prüfen ob bereits ein Eintrag für diesen Abschnitt existiert
    $checkSql = "SELECT COUNT(*) FROM [dbo].[abschnitte_bewertung] WHERE [abschnitte-id] = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$abschnittId]);
    $exists = $checkStmt->fetchColumn() > 0;
    
    if ($exists) {
        // UPDATE
        $sql = "UPDATE [dbo].[abschnitte_bewertung] SET [$field] = ? WHERE [abschnitte-id] = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$value, $abschnittId]);
    } else {
        // INSERT - Alle erforderlichen Felder mit Standardwerten setzen
        $sql = "INSERT INTO [dbo].[abschnitte_bewertung] ([abschnitte-id], [strasse], [gehweg_links], [gehweg_rechts], [seitenstreifen_links], [seitenstreifen_rechts], [review], [schaden], [text]) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        // Standardwerte für alle Felder setzen, nur das aktuelle Feld mit dem echten Wert
        $defaultValues = [
            'strasse' => 0,
            'gehweg_links' => 0,
            'gehweg_rechts' => 0,
            'seitenstreifen_links' => 0,
            'seitenstreifen_rechts' => 0,
            'review' => 0,
            'schaden' => 0,
            'text' => ''
        ];
        
        // Den aktuellen Wert für das geänderte Feld setzen
        $defaultValues[$field] = $value;
        
        $stmt->execute([
            $abschnittId,
            $defaultValues['strasse'],
            $defaultValues['gehweg_links'],
            $defaultValues['gehweg_rechts'],
            $defaultValues['seitenstreifen_links'],
            $defaultValues['seitenstreifen_rechts'],
            $defaultValues['review'],
            $defaultValues['schaden'],
            $defaultValues['text']
        ]);
    }
    
    // Log-Eintrag erstellen
    createLogEntry($conn, $abschnittId, $field, $value);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log('Fehler beim Speichern der Abschnittsbewertung: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Funktion zum Erstellen eines Log-Eintrags
function createLogEntry($conn, $abschnittId, $field, $value) {
    try {
        // Benutzername aus Session oder Server-Variablen
        $username = 'Unbekannt';
        if (!empty($_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"])) {
            $useremail = $_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"];
            $username = explode('@', $useremail)[0];
            $username = ucwords(str_replace('.', ' ', $username));
        } elseif (isset($_SERVER['LOGON_USER'])) {
            $username = $_SERVER['LOGON_USER'];
        } elseif (isset($_SESSION['USER_NAME'])) {
            $username = $_SESSION['USER_NAME'];
        }
        
        // Aktueller Zeitstempel in CET
        $dateTime = new DateTime('now', new DateTimeZone('Europe/Berlin'));
        $createdAt = $dateTime->format('Y-m-d H:i:s');
        
        // Für Text-Felder: Wert auf -1 setzen (da Wert-Spalte int ist)
        // Der eigentliche Text wird in der abschnitte_bewertung.text gespeichert
        $logValue = ($field === 'text') ? -1 : $value;
        
        $sql = "INSERT INTO [dbo].[log_abschnitte_bewertung] ([abschnitte_id], [Feld], [Wert], [Nutzer], [Zeitstempel]) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$abschnittId, $field, $logValue, $username, $createdAt]);
        
    } catch (Exception $e) {
        error_log('Fehler beim Erstellen des Log-Eintrags: ' . $e->getMessage());
        throw $e;
    }
}
?> 
