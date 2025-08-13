0727

<?php
// Autoload für Composer (falls du vlucas/phpdotenv verwendest)
require 'vendor/autoload.php';

use Dotenv\Dotenv;

// Lade die .env-Datei
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Variablen ausgeben
echo "DB_SERVER: " . $_ENV['DB_SERVER'] . PHP_EOL;
echo "DB_NAME: " . $_ENV['DB_NAME'] . PHP_EOL;
echo "DB_USER: " . $_ENV['DB_USER'] . PHP_EOL;
echo "DB_PASS: " . $_ENV['DB_PASS'] . PHP_EOL;
?>

