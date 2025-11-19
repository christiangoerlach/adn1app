<?php
/**
 * Project Controller
 * Steuert die Logik für Projektoperationen
 */

require_once __DIR__ . '/../model/Project.php';
require_once __DIR__ . '/../model/Image.php';

class ProjectController {
    private $projectModel;
    private $imageModel;
    
    public function __construct() {
        $this->projectModel = new Project();
        $this->imageModel = new Image();
    }
    
    /**
     * Zeigt die Hauptseite mit Projektauswahl
     */
    public function index() {
        // Session starten
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Benutzerinformationen abrufen
        $userInfo = $this->getUserInfo();
        
        // Projekte abrufen
        $projects = $this->projectModel->getAllProjects();
        
        // Aktuelles Projekt abrufen
        $currentProject = null;
        $imageCount = 0;
        
        if (!empty($_SESSION['PROJEKT_ID'])) {
            $currentProject = $this->projectModel->getProjectById($_SESSION['PROJEKT_ID']);
            if ($currentProject) {
                $imageCount = $this->imageModel->getImageCountByProject($_SESSION['PROJEKT_ID']);
            }
        }
        
        // View laden
        require_once __DIR__ . '/../view/project/index.php';
    }
    
    /**
     * Verarbeitet die Projektauswahl
     */
    public function selectProject() {
        // Setze Content-Type für JSON-Response
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['auswahl'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Ungültige Anfrage']);
            exit;
        }
        
        $projectId = (int) $_POST['auswahl'];
        
        if ($projectId > 0 && $this->projectModel->projectExists($projectId)) {
            // Session starten
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Projekt-Container abrufen
            $container = $this->projectModel->getBilderContainer($projectId);
            
            if ($container) {
                $_SESSION['PROJEKT_ID'] = $projectId;
                $_SESSION['AZURE_STORAGE_CONTAINER_NAME'] = $container;
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Kein Bilder-Container gefunden']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Ungültige Projekt-ID']);
        }
        exit;
    }
    
    /**
     * Zeigt die Projektübersicht
     */
    public function showProjectOverview() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['PROJEKT_ID'])) {
            echo '<p>Kein Projekt ausgewählt.</p>';
            return;
        }
        
        $project = $this->projectModel->getProjectById($_SESSION['PROJEKT_ID']);
        $imageCount = $this->imageModel->getImageCountByProject($_SESSION['PROJEKT_ID']);
        
        require_once __DIR__ . '/../view/project/overview.php';
    }
    
    /**
     * Holt Benutzerinformationen aus verschiedenen Quellen
     * @return array Benutzerinformationen
     */
    private function getUserInfo() {
        $username = 'Gast';
        $useremail = '';
        
        if (!empty($_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"])) {
            $useremail = $_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"];
            $username = explode('@', $useremail)[0];
            $username = ucwords(str_replace('.', ' ', $username));
        } elseif (isset($_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL'])) {
            $principal = json_decode(base64_decode($_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL']), true);
            if (isset($principal['userDetails'])) {
                $useremail = $principal['userDetails'];
            }
            if (isset($principal['name'])) {
                $username = $principal['name'];
            }
        } elseif (isset($_SERVER['LOGON_USER'])) {
            $username = $_SERVER['LOGON_USER'];
        } elseif (isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
        }
        
        return [
            'username' => $username,
            'useremail' => $useremail
        ];
    }
}


