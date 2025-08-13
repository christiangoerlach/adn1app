0731

<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

// Lade Umgebungsvariablen
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Hole Variablen
$serverName = $_ENV['DB_SERVER'];
$database = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

// Verbindung herstellen
try {
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Verbindung erfolgreich hergestellt.\n\n";

    // Query ausführen
    $sql = "SELECT * FROM [dbo].[ImageRegistry]";
    $stmt = $conn->query($sql);

    // Ergebnisse anzeigen
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
        echo "\n";
    }

} catch (PDOException $e) {
    echo "❌ Fehler bei der Verbindung: " . $e->getMessage();
}
?>


