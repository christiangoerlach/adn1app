<?php
/**
 * Allgemeine Anwendungskonfiguration
 * Enthält alle App-spezifischen Einstellungen
 */

// Lade Umgebungsvariablen
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Anwendungseinstellungen
define('APP_NAME', 'A.D.N. StraßenWeb');
define('APP_VERSION', '2.0.0');

// Build-Datum: Automatisch aus Git oder Filemtime berechnen
// Falls Git verfügbar ist, nutze das letzte Commit-Datum
// Ansonsten nutze das Änderungsdatum dieser Datei
if (!defined('APP_BUILD_DATE')) {
    $buildDate = null;
    
    // Versuche Git-Log zu verwenden (am genauesten) - mit Uhrzeit
    if (function_exists('shell_exec') && file_exists(__DIR__ . '/../.git')) {
        // Git log mit Datum und Uhrzeit (UTC)
        $gitCommand = 'git log -1 --format=%cd --date=iso-strict';
        // Für Windows: Umleitung von stderr
        if (PHP_OS_FAMILY === 'Windows') {
            $gitCommand .= ' 2>nul';
        } else {
            $gitCommand .= ' 2>/dev/null';
        }
        $gitDate = @shell_exec($gitCommand);
        if ($gitDate) {
            $gitDate = trim($gitDate);
            // Speichere immer in UTC für Konsistenz zwischen localhost und Azure
            $dateTime = new DateTime($gitDate, new DateTimeZone('UTC'));
            $buildDate = $dateTime->format('Y-m-d H:i:s');
        }
    }
    
    // Fallback: Nutze Filemtime dieser Datei mit Uhrzeit
    // Konvertiere immer in UTC für Konsistenz zwischen localhost und Azure
    if (!$buildDate) {
        $timestamp = filemtime(__FILE__);
        $dateTime = new DateTime('@' . $timestamp, new DateTimeZone('UTC'));
        $buildDate = $dateTime->format('Y-m-d H:i:s');
    }
    
    define('APP_BUILD_DATE', $buildDate);
}

define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? false);

// Pfade
define('BASE_PATH', __DIR__ . '/..');
define('PHP_PATH', BASE_PATH . '/php');
define('VIEW_PATH', PHP_PATH . '/view');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Base URL für statische Assets (CSS, JS, etc.)
// Erkennt automatisch, ob wir in Docker (nginx root = /var/www/html/public) oder Azure sind
function getBaseUrl() {
    // Prüfe ob wir über Webserver aufgerufen werden (DOCUMENT_ROOT ist gesetzt)
    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    
    if (!empty($docRoot)) {
        // Wenn DOCUMENT_ROOT auf /public endet oder public enthält, 
        // dann ist nginx/Apache root = public, also BASE_URL = ''
        if (strpos($docRoot, '/public') !== false || 
            strpos($docRoot, '\public') !== false ||
            substr($docRoot, -7) === '/public' ||
            substr($docRoot, -7) === '\public') {
            return ''; // Leerer String = Root-Relativ
        }
    }
    
    // Fallback: Prüfe ob wir im Web-Kontext sind durch REQUEST_URI oder SCRIPT_NAME
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // Wenn SCRIPT_NAME mit /index.php beginnt (nicht /public/index.php), 
    // dann müssen wir /public voranstellen (typisch für Azure)
    if (!empty($scriptName) && strpos($scriptName, '/public/') === false && strpos($scriptName, '/index.php') !== false) {
        // Aber prüfe zuerst, ob die Datei direkt erreichbar ist
        // Wenn /css/main.css existiert (ohne /public), dann ist BASE_URL = ''
        return '/public';
    }
    
    // Standard: Wenn nichts passt, verwende /public für Sicherheit
    // In Docker wird DOCUMENT_ROOT normalerweise /var/www/html/public sein
    // und die Funktion sollte vorher returnen
    return '/public';
}

// Definiere BASE_URL für Views
if (!defined('BASE_URL')) {
    define('BASE_URL', getBaseUrl());
}

// Azure-Konfiguration
define('AZURE_MAPS_KEY', $_ENV['AZURE_MAPS_KEY'] ?? '');
define('AZURE_STORAGE_CONNECTION_STRING', $_ENV['AZURE_STORAGE_CONNECTION_STRING'] ?? '');

// Session-Einstellungen
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);

// Fehlerbehandlung
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Zeitzone
date_default_timezone_set('Europe/Berlin');

// Autoloader für eigene Klassen
spl_autoload_register(function ($class) {
    $file = PHP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});


