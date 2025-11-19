<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$serverName = $_ENV['DB_SERVER'];
$database   = $_ENV['DB_NAME'];
$username   = $_ENV['DB_USER'];
$password   = $_ENV['DB_PASS'];

try {
    // TrustServerCertificate für lokale Entwicklung mit selbstsignierten Zertifikaten
    $connectionString = "sqlsrv:Server=$serverName;Database=$database;TrustServerCertificate=yes";
    $conn = new PDO($connectionString, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional: echo "Verbindung erfolgreich.";
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}
?>