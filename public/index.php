<?php
/**
 * Haupteinstiegspunkt für ADN StraßenWeb
 * Verwendet MVC-Architektur
 */

// Debug: Sofortige Ausgabe zum Testen (temporär)
// Dies zeigt, ob die Datei überhaupt erreicht wird
if (isset($_GET['debug']) || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'debug') !== false)) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Debug-Informationen</h1>';
    echo '<pre>';
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
    echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
    echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'N/A') . "\n";
    echo "REDIRECT_URL: " . ($_SERVER['REDIRECT_URL'] ?? 'N/A') . "\n";
    echo "ORIG_PATH_INFO: " . ($_SERVER['ORIG_PATH_INFO'] ?? 'N/A') . "\n";
    echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
    echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
    echo "__DIR__: " . __DIR__ . "\n";
    echo "\nAlle \$_SERVER Variablen:\n";
    echo print_r($_SERVER, true);
    echo '</pre>';
    exit;
}

// Konfiguration laden
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Controller laden
require_once __DIR__ . '/../php/controller/HomeController.php';
require_once __DIR__ . '/../php/controller/ProjectController.php';
require_once __DIR__ . '/../php/controller/NetzknotenController.php';
require_once __DIR__ . '/../php/controller/AbschnittController.php';

// Routing über GET-Parameter
$path = $_GET['path'] ?? '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Einfaches Routing basierend auf path-Parameter
if ($path === '' || $path === 'index.php') {
    // Neue Übersichtsseite
    $controller = new HomeController();
    $controller->index();
} elseif ($path === 'bewertungm') {
    // Projektauswahl-Seite für manuelle Bildbewertung
    $controller = new ProjectController();
    
    if ($requestMethod === 'POST' && isset($_POST['auswahl'])) {
        $controller->selectProject();
    } else {
        $controller->index();
    }
} elseif ($path === 'netzknoten') {
    // Netzknoten Modell - Projektauswahl-Seite
    $controller = new NetzknotenController();
    
    if ($requestMethod === 'POST' && isset($_POST['auswahl'])) {
        $controller->selectProject();
    } else {
        $controller->index();
    }
} elseif ($path === 'abschnitt') {
    // Abschnittsbewertung - Projektauswahl-Seite
    $controller = new AbschnittController();
    
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
    header('Content-Type: text/html; charset=utf-8');
    http_response_code(404);
    ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>404 - Seite nicht gefunden</title>
</head>
<body>
    <h1>404 - Seite nicht gefunden</h1>
    <p>Die angeforderte Seite existiert nicht.</p>
    <p><a href="/index.php">Zurück zur Übersicht</a></p>
</body>
</html>
<?php
    exit;
}
