<?php
/**
 * Router für PHP Built-in Server
 * Leitet alle Anfragen an index.php weiter (außer existierende Dateien)
 */

// Prüfe ob die angeforderte Datei existiert
$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$filePath = __DIR__ . $uri;

// Wenn die Datei existiert und eine echte Datei ist (nicht ein Verzeichnis), direkt ausliefern
if ($uri !== '/' && file_exists($filePath) && is_file($filePath)) {
    return false; // PHP Built-in Server liefert die Datei direkt aus
}

// Alle anderen Anfragen an index.php weiterleiten
require_once __DIR__ . '/index.php';

