<?php
/**
 * Einstiegspunkt: Leitet alle Anfragen an public/index.php weiter
 * Diese Datei stellt sicher, dass die neue MVC-Architektur verwendet wird
 */

// Stelle sicher, dass der REQUEST_URI korrekt gesetzt ist
// Falls nicht, setze ihn basierend auf SCRIPT_NAME und PATH_INFO
if (!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI'])) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
    $queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    $_SERVER['REQUEST_URI'] = $scriptName . $pathInfo . $queryString;
}

// Leite an public/index.php weiter
require_once __DIR__ . '/public/index.php';