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
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Entferne Query-String und führende Slashes
$path = parse_url($requestUri, PHP_URL_PATH);
if ($path === false) {
    $path = '/';
}
$path = trim($path, '/');

// Azure: Wenn SCRIPT_NAME mit /index.php endet, könnte der Pfad falsch sein
// In diesem Fall versuchen wir, den Pfad aus REQUEST_URI zu extrahieren
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
if (strpos($scriptName, '/index.php') !== false && $path === 'index.php') {
    // Versuche, den echten Pfad zu extrahieren
    if (isset($_SERVER['REDIRECT_URL'])) {
        $path = trim($_SERVER['REDIRECT_URL'], '/');
    } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
        $path = trim($_SERVER['ORIG_PATH_INFO'], '/');
    }
}

// Debug: Wenn wir von der Root index.php kommen, sollte der Pfad bereits korrekt sein
// Falls der Pfad mit "public/" beginnt, entferne diesen Präfix (falls durch Root index.php weitergeleitet)
if (strpos($path, 'public/') === 0) {
    $path = substr($path, 7); // "public/" = 7 Zeichen entfernen
}

// Azure-spezifisch: Falls APP_BASE_PATH gesetzt ist und der Pfad damit beginnt, entferne es
if (defined('APP_BASE_PATH')) {
    $basePathValue = constant('APP_BASE_PATH');
    if (!empty($basePathValue) && $basePathValue !== '/') {
        $basePath = trim($basePathValue, '/');
        if (!empty($basePath) && strpos($path, $basePath . '/') === 0) {
            $path = substr($path, strlen($basePath) + 1);
        } elseif ($path === $basePath) {
            $path = '';
        }
    }
}

// Debug-Modus: Immer aktiv für Diagnose (kann später entfernt werden)
error_log("Routing Debug - REQUEST_URI: $requestUri, Path: $path, SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A'));

// Einfaches Routing
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
} elseif (strpos($path, 'bewertung/') === 0) {
    // Alte Bewertungsdateien direkt laden (z.B. bewertung/bewertung.php, bewertung/abschnitt-bewertung.php)
    // Entferne "bewertung/" Präfix und füge .php hinzu (falls nicht vorhanden)
    $fileName = substr($path, 10); // "bewertung/" = 10 Zeichen
    // Entferne .php falls bereits vorhanden
    if (substr($fileName, -4) === '.php') {
        $fileName = substr($fileName, 0, -4);
    }
    
    // Korrigierter Pfad: Von public/index.php zu bewertung/
    $filePath = realpath(__DIR__ . '/../bewertung/' . $fileName . '.php');
    
    // Sicherheitsprüfung: Nur Dateien im bewertung/ Ordner erlauben
    $allowedDir = realpath(__DIR__ . '/../bewertung');
    
    if ($filePath && strpos($filePath, $allowedDir) === 0 && file_exists($filePath)) {
        require_once $filePath;
        exit;
    } else {
        http_response_code(404);
        echo '<h1>404 - Datei nicht gefunden</h1>';
        echo '<p>Die angeforderte Datei existiert nicht.</p>';
        echo '<p>Gesuchter Pfad: ' . htmlspecialchars(__DIR__ . '/../bewertung/' . $fileName . '.php') . '</p>';
        if ($filePath) {
            echo '<p>Realpath: ' . htmlspecialchars($filePath) . '</p>';
        }
        if ($allowedDir) {
            echo '<p>Allowed Dir: ' . htmlspecialchars($allowedDir) . '</p>';
        }
        echo '<p><a href="/">Zurück zur Übersicht</a></p>';
        exit;
    }
} elseif ($path === 'bewertung') {
    // Alte Bewertungsseite mit Filter-Parametern
    $filePath = __DIR__ . '/../bewertung/bewertung.php';
    if (file_exists($filePath)) {
        require_once $filePath;
        exit;
    } else {
        // Fallback: Neue MVC-Route
        require_once __DIR__ . '/../php/controller/BewertungController.php';
        $controller = new BewertungController();
        $controller->index();
    }
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
} elseif (strpos($path, 'bewertung/') === false && file_exists(__DIR__ . '/../bewertung/' . basename($path) . '.php')) {
    // Alte Bewertungsdateien im bewertung-Ordner (z.B. bilder.php, get_bewertung.php) direkt laden
    $fileName = basename($path);
    if (substr($fileName, -4) !== '.php') {
        $fileName .= '.php';
    }
    $filePath = __DIR__ . '/../bewertung/' . $fileName;
    
    if (file_exists($filePath)) {
        require_once $filePath;
        exit;
    }
} else {
    // 404 - Seite nicht gefunden
    http_response_code(404);
    echo '<h1>404 - Seite nicht gefunden</h1>';
    echo '<p>Die angeforderte Seite existiert nicht.</p>';
    // Temporär: Immer Debug-Informationen anzeigen
    echo '<p><strong>Debug-Informationen:</strong></p>';
    echo '<ul>';
    echo '<li>REQUEST_URI: ' . htmlspecialchars($requestUri) . '</li>';
    echo '<li>Erkannter Pfad: ' . htmlspecialchars($path) . '</li>';
    echo '<li>REQUEST_METHOD: ' . htmlspecialchars($requestMethod) . '</li>';
    echo '<li>SCRIPT_NAME: ' . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? 'N/A') . '</li>';
    echo '<li>PHP_SELF: ' . htmlspecialchars($_SERVER['PHP_SELF'] ?? 'N/A') . '</li>';
    echo '<li>PATH_INFO: ' . htmlspecialchars($_SERVER['PATH_INFO'] ?? 'N/A') . '</li>';
    echo '<li>QUERY_STRING: ' . htmlspecialchars($_SERVER['QUERY_STRING'] ?? 'N/A') . '</li>';
    echo '<li>HTTP_HOST: ' . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A') . '</li>';
    echo '<li>REDIRECT_URL: ' . htmlspecialchars($_SERVER['REDIRECT_URL'] ?? 'N/A') . '</li>';
    echo '<li>ORIG_PATH_INFO: ' . htmlspecialchars($_SERVER['ORIG_PATH_INFO'] ?? 'N/A') . '</li>';
    if (defined('APP_BASE_PATH')) {
        echo '<li>APP_BASE_PATH: ' . htmlspecialchars(constant('APP_BASE_PATH')) . '</li>';
    }
    echo '<li>DOCUMENT_ROOT: ' . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . '</li>';
    echo '<li>__DIR__: ' . htmlspecialchars(__DIR__) . '</li>';
    echo '</ul>';
    echo '<p><a href="/">Zurück zur Übersicht</a></p>';
}
