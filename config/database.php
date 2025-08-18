<?php
/**
 * Datenbankkonfiguration
 * Enthält alle Datenbankeinstellungen und stellt die Verbindung her
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Lade Umgebungsvariablen
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Datenbankeinstellungen aus Umgebungsvariablen
$serverName = $_ENV['DB_SERVER'];
$database   = $_ENV['DB_NAME'];
$username   = $_ENV['DB_USER'];
$password   = $_ENV['DB_PASS'];

// Globale Datenbankverbindung
$conn = null;

/**
 * Stellt die Datenbankverbindung her
 * @return PDO Die Datenbankverbindung
 * @throws PDOException Bei Verbindungsfehlern
 */
function getDatabaseConnection() {
    global $conn, $serverName, $database, $username, $password;
    
    if ($conn === null) {
        try {
            $conn = new PDO("sqlsrv:Server=$serverName;Database=$database", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
            throw new PDOException("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
        }
    }
    
    return $conn;
}

// Initialisiere die Verbindung
try {
    $conn = getDatabaseConnection();
} catch (PDOException $e) {
    // Bei Fehlern wird die Verbindung später erneut versucht
    error_log("Initiale Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}

