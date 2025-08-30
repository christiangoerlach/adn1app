<?php
header('Content-Type: application/json');

// Session starten
session_start();

// Datenbankverbindung
require_once '../db.php';

// Bild-ID aus GET-Parameter
$bildId = isset($_GET['bildId']) ? intval($_GET['bildId']) : 0;

if (!$bildId) {
    echo json_encode([
        'success' => false,
        'error' => 'Keine Bild-ID angegeben'
    ]);
    exit;
}

try {
    // Koordinaten aus der Datenbank abrufen
    $stmt = $conn->prepare("
        SELECT [koordinate-x], [koordinate-y] 
        FROM [dbo].[bilder] 
        WHERE Id = ?
    ");
    $stmt->execute([$bildId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $x = $row['koordinate-x'];
        $y = $row['koordinate-y'];
        
        // Prüfen ob gültige Koordinaten vorhanden sind
        if ($x && $y && $x != 0 && $y != 0) {
            // Koordinaten von großen Zahlen zu Dezimalzahlen konvertieren
            // Beispiel: 8821008602255184 -> 8.821008602255184
            $x_decimal = $x / 1000000000000000; // 15 Nullen entfernen
            $y_decimal = $y / 1000000000000000; // 15 Nullen entfernen
            
            // CRS84/WGS84 Koordinaten: [latitude, longitude] für Leaflet
            // X = Länge (Longitude), Y = Breite (Latitude)
            echo json_encode([
                'success' => true,
                'coordinates' => [
                    'lat' => $y_decimal,  // Breite (Latitude) = Y
                    'lng' => $x_decimal   // Länge (Longitude) = X
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Keine gültigen Koordinaten verfügbar'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Bild nicht gefunden'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}
?>
