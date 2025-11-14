<?php
/**
 * Einstiegspunkt: Leitet alle Anfragen an public/index.php weiter
 * Diese Datei stellt sicher, dass die neue MVC-Architektur verwendet wird
 */

// Stelle sicher, dass der REQUEST_URI korrekt gesetzt ist
// Falls die Anfrage über .htaccess weitergeleitet wurde, könnte REQUEST_URI falsch sein
if (!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI'])) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
    $queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    $_SERVER['REQUEST_URI'] = $scriptName . $pathInfo . $queryString;
}

// Wenn REQUEST_URI nur /index.php ist, aber REDIRECT_URL gesetzt ist, nutze diesen
if (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REQUEST_URI'] === '/index.php') {
    $queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_URL'] . $queryString;
}

// Leite an public/index.php weiter
require_once __DIR__ . '/public/index.php';