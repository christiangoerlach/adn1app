<?php
// Fehlerausgabe aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>=== Debug: Bildergalerie Datenprüfung ===\n\n";

// 1. DB-Verbindung testen
echo "1) Verbindung zur Datenbank herstellen...\n";
require_once 'db.php'; // muss $conn als PDO liefern

if (!$conn) {
    die("❌ Keine Verbindung zur Datenbank.\n");
}
echo "✅ Verbindung erfolgreich.\n\n";

// 2. DB-Abfrage testen
echo "2) Hole FileName aus [dbo].[ImageRegistry]...\n";
try {
    $sql = "SELECT [FileName] FROM [dbo].[ImageRegistry]";
    $stmt = $conn->query($sql);

    if (!$stmt) {
        $errorInfo = $conn->errorInfo();
        die("❌ SQL-Fehler: " . implode(" | ", $errorInfo) . "\n");
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($rows);
    echo "✅ $count Einträge gefunden.\n\n";

    if ($count === 0) {
        die("⚠️ Keine Bilder in der Tabelle gefunden.\n");
    }
} catch (PDOException $e) {
    die("❌ Datenbankfehler: " . $e->getMessage() . "\n");
}

// 3. Azure-Blob-Links erzeugen
$blobBaseUrl = "https://cgml1519944712.blob.core.windows.net/adntest/";
$urls = [];
echo "3) Erzeuge Azure-Links...\n";
foreach ($rows as $row) {
    $fileName = $row['FileName'];
    $url = $blobBaseUrl . rawurlencode($fileName);
    $urls[] = $url;
    echo "🔗 $url\n";
}
echo "✅ Links erzeugt.\n\n";

// 4. Teste ob die Links erreichbar sind (nur HEAD-Request)
echo "4) Prüfe Erreichbarkeit der Dateien...\n";
foreach ($urls as $url) {
    $headers = @get_headers($url);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "✅ OK: $url\n";
    } else {
        echo "❌ Fehler: $url (";
        echo $headers[0] ?? 'Keine Antwort';
        echo ")\n";
    }
}

echo "\n=== Debug-Ende ===\n</pre>";

// 5. Falls alles ok, zusätzlich JSON ausgeben (für fetch())
if (isset($_GET['json']) && $_GET['json'] == 1) {
    header('Content-Type: application/json');
    echo json_encode($urls, JSON_UNESCAPED_SLASHES);
}

?>
