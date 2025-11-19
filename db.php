<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Lade Umgebungsvariablen
// Docker-Umgebungsvariablen (aus docker-compose.yml) haben Vorrang
// Falls nicht gesetzt, wird .env Datei verwendet
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad(); // safeLoad überschreibt keine existierenden Umgebungsvariablen

// Datenbankeinstellungen aus Umgebungsvariablen
// Priorität: 1. getenv() (Docker/Azure), 2. $_ENV (Dotenv), 3. Fallback
$serverName = getenv('DB_SERVER') ?: ($_ENV['DB_SERVER'] ?? '');
$database   = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? '');
$username   = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? '');
$password   = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? '');

if (empty($serverName) || empty($database) || empty($username)) {
    die("Datenbankverbindung fehlgeschlagen: Fehlende Konfiguration (DB_SERVER, DB_NAME, DB_USER)");
}

try {
    // TrustServerCertificate nur für lokale Entwicklung (Server enthält "db" oder "localhost")
    // Für Azure-Datenbanken wird das Zertifikat normalerweise verifiziert
    $isLocalDb = (strpos($serverName, 'db') !== false && strpos($serverName, 'database.windows.net') === false) 
              || strpos($serverName, 'localhost') !== false
              || strpos($serverName, '127.0.0.1') !== false;
    
    $connectionString = "sqlsrv:Server=$serverName;Database=$database";
    if ($isLocalDb) {
        $connectionString .= ";TrustServerCertificate=yes";
    }
    
    $conn = new PDO($connectionString, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Optional: echo "Verbindung erfolgreich.";
} catch (PDOException $e) {
    error_log("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}
?>