<?php
/**  
 * Router für PHP Built-in Server.
 * Leitet alle Anfragen an index.php weiter (außer existierende Dateien)
 */

// Prüfe ob die angeforderte Datei existiert
$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$filePath = __DIR__ . $uri;
$rootFilePath = dirname(__DIR__) . $uri;

// Wenn die Datei im public-Verzeichnis existiert, direkt ausliefern
if ($uri !== '/' && file_exists($filePath) && is_file($filePath)) {
    return false; // PHP Built-in Server liefert die Datei direkt aus
}

// Wenn die Datei im Root-Verzeichnis existiert, manuell laden und ausgeben
if ($uri !== '/' && file_exists($rootFilePath) && is_file($rootFilePath)) {
    // Sicherheitsprüfung: Nur Dateien im bewertung-Verzeichnis und andere erlaubte Verzeichnisse erlauben
    $allowedDirs = ['bewertung', 'zuordnung', 'shape_convert'];
    $firstSegment = trim(explode('/', $uri)[1] ?? '', '/');
    
    if (in_array($firstSegment, $allowedDirs)) {
        // Prüfe ob die Datei wirklich im erlaubten Verzeichnis liegt
        $realPath = realpath($rootFilePath);
        $rootDir = realpath(dirname(__DIR__));
        
        if ($realPath && strpos($realPath, $rootDir) === 0) {
            // Datei laden und ausgeben
            $ext = pathinfo($rootFilePath, PATHINFO_EXTENSION);
            
            // MIME-Type setzen
            $mimeTypes = [
                'php' => 'text/html',
                'html' => 'text/html',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
            ];
            
            $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
            header('Content-Type: ' . $mimeType . '; charset=utf-8');
            
            // PHP-Dateien ausführen, andere Dateien direkt ausgeben
            if ($ext === 'php') {
                require_once $rootFilePath;
            } else {
                readfile($rootFilePath);
            }
            exit;
        }
    }
}

// Alle anderen Anfragen an index.php weiterleiten
require_once __DIR__ . '/index.php';

