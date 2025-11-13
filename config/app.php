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
    
    // Versuche Git-Log zu verwenden (am genauesten)
    if (function_exists('shell_exec') && file_exists(__DIR__ . '/../.git')) {
        $gitCommand = 'git log -1 --format=%cd --date=short';
        // Für Windows: Umleitung von stderr
        if (PHP_OS_FAMILY === 'Windows') {
            $gitCommand .= ' 2>nul';
        } else {
            $gitCommand .= ' 2>/dev/null';
        }
        $gitDate = @shell_exec($gitCommand);
        if ($gitDate) {
            $buildDate = trim($gitDate);
        }
    }
    
    // Fallback: Nutze Filemtime dieser Datei
    if (!$buildDate) {
        $buildDate = date('Y-m-d', filemtime(__FILE__));
    }
    
    define('APP_BUILD_DATE', $buildDate);
}

define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? false);

// Pfade
define('BASE_PATH', __DIR__ . '/..');
define('PHP_PATH', BASE_PATH . '/php');
define('VIEW_PATH', PHP_PATH . '/view');
define('PUBLIC_PATH', BASE_PATH . '/public');

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


