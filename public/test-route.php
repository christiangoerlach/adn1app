<?php
/**
 * Test-Datei um zu prüfen, ob Routing funktioniert
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Route Test</title>
</head>
<body>
    <h1>Route Test erfolgreich!</h1>
    <p>Diese Datei wurde erfolgreich erreicht.</p>
    <h2>Server-Informationen:</h2>
    <pre>
REQUEST_URI: <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') ?>

SCRIPT_NAME: <?= htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? 'N/A') ?>

PHP_SELF: <?= htmlspecialchars($_SERVER['PHP_SELF'] ?? 'N/A') ?>

DOCUMENT_ROOT: <?= htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') ?>

HTTP_HOST: <?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A') ?>

__FILE__: <?= htmlspecialchars(__FILE__) ?>

__DIR__: <?= htmlspecialchars(__DIR__) ?>
    </pre>
    
    <h2>Alle $_SERVER Variablen:</h2>
    <pre><?= htmlspecialchars(print_r($_SERVER, true)) ?></pre>
    
    <p><a href="/">Zurück zur Startseite</a></p>
</body>
</html>

