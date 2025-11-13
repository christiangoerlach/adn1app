<?php
/**
 * Haupteinstiegspunkt für ADN StraßenWeb
 * Verwendet MVC-Architektur
 */

// Konfiguration laden
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Controller laden
require_once __DIR__ . '/../php/controller/HomeController.php';
require_once __DIR__ . '/../php/controller/ProjectController.php';

// Routing
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Entferne Query-String und führende Slashes
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');

// Einfaches Routing
if ($path === '' || $path === 'index.php') {
    // Neue Übersichtsseite
    $controller = new HomeController();
    $controller->index();
} elseif ($path === 'projekt') {
    // Projektauswahl-Seite (alte Startseite)
    $controller = new ProjectController();
    
    if ($requestMethod === 'POST' && isset($_POST['auswahl'])) {
        $controller->selectProject();
    } else {
        $controller->index();
    }
} elseif ($path === 'bewertung') {
    // Bewertungsseite
    require_once __DIR__ . '/../php/controller/BewertungController.php';
    $controller = new BewertungController();
    $controller->index();
} elseif ($path === 'map') {
    // Kartenansicht
    require_once __DIR__ . '/../php/controller/MapController.php';
    $controller = new MapController();
    $controller->index();
} elseif ($path === 'map-old') {
    // Alte Kartenansicht
    require_once __DIR__ . '/../php/controller/MapController.php';
    $controller = new MapController();
    $controller->showOld();
} elseif ($path === 'api' && isset($_GET['action'])) {
    // API-Endpunkte
    $action = $_GET['action'];
    
    if ($action === 'project-overview') {
        $controller = new ProjectController();
        $controller->showProjectOverview();
    } elseif ($action === 'rate-image') {
        require_once __DIR__ . '/../php/controller/BewertungController.php';
        $controller = new BewertungController();
        $controller->rateImage();
    } elseif ($action === 'next-image') {
        require_once __DIR__ . '/../php/controller/BewertungController.php';
        $controller = new BewertungController();
        $controller->getNextImage();
    } elseif ($action === 'previous-image') {
        require_once __DIR__ . '/../php/controller/BewertungController.php';
        $controller = new BewertungController();
        $controller->getPreviousImage();
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API-Endpunkt nicht gefunden']);
    }
} else {
    // 404 - Seite nicht gefunden
    http_response_code(404);
    echo '<h1>404 - Seite nicht gefunden</h1>';
    echo '<p>Die angeforderte Seite existiert nicht.</p>';
    echo '<p><a href="/">Zurück zur Übersicht</a></p>';
}
