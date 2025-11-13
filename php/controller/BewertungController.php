<?php
/**
 * Bewertung Controller
 * Steuert die Logik für die Bildbewertung
 */

require_once __DIR__ . '/../model/Project.php';
require_once __DIR__ . '/../model/Image.php';

class BewertungController {
    private $projectModel;
    private $imageModel;
    
    public function __construct() {
        $this->projectModel = new Project();
        $this->imageModel = new Image();
    }
    
    /**
     * Zeigt die Bewertungsseite
     */
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['PROJEKT_ID'])) {
            header('Location: /bewertungm');
            exit;
        }
        
        $project = $this->projectModel->getProjectById($_SESSION['PROJEKT_ID']);
        if (!$project) {
            header('Location: /bewertungm');
            exit;
        }
        
        $images = $this->imageModel->getImagesByProject($_SESSION['PROJEKT_ID']);
        
        require_once __DIR__ . '/../view/bewertung/index.php';
    }
    
    /**
     * Verarbeitet eine Bildbewertung
     */
    public function rateImage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Methode nicht erlaubt');
        }
        
        $imageId = (int) ($_POST['image_id'] ?? 0);
        $rating = $_POST['rating'] ?? '';
        
        if ($imageId <= 0 || empty($rating)) {
            echo json_encode(['success' => false, 'error' => 'Ungültige Parameter']);
            exit;
        }
        
        // Bewertung in der Datenbank speichern
        if ($this->imageModel->updateRating($imageId, $rating)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Fehler beim Speichern der Bewertung']);
        }
        exit;
    }
    
    /**
     * Holt das nächste Bild für die Bewertung
     */
    public function getNextImage() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['PROJEKT_ID'])) {
            echo json_encode(['success' => false, 'error' => 'Kein Projekt ausgewählt']);
            exit;
        }
        
        $currentImageId = (int) ($_GET['current_id'] ?? 0);
        $images = $this->imageModel->getImagesByProject($_SESSION['PROJEKT_ID']);
        
        if (empty($images)) {
            echo json_encode(['success' => false, 'error' => 'Keine Bilder gefunden']);
            exit;
        }
        
        // Finde das nächste Bild
        $nextImage = null;
        if ($currentImageId > 0) {
            foreach ($images as $index => $image) {
                if ($image['Id'] == $currentImageId) {
                    $nextIndex = ($index + 1) % count($images);
                    $nextImage = $images[$nextIndex];
                    break;
                }
            }
        }
        
        if (!$nextImage) {
            $nextImage = $images[0];
        }
        
        echo json_encode([
            'success' => true,
            'image' => $nextImage,
            'total' => count($images)
        ]);
        exit;
    }
    
    /**
     * Holt das vorherige Bild für die Bewertung
     */
    public function getPreviousImage() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['PROJEKT_ID'])) {
            echo json_encode(['success' => false, 'error' => 'Kein Projekt ausgewählt']);
            exit;
        }
        
        $currentImageId = (int) ($_GET['current_id'] ?? 0);
        $images = $this->imageModel->getImagesByProject($_SESSION['PROJEKT_ID']);
        
        if (empty($images)) {
            echo json_encode(['success' => false, 'error' => 'Keine Bilder gefunden']);
            exit;
        }
        
        // Finde das vorherige Bild
        $previousImage = null;
        if ($currentImageId > 0) {
            foreach ($images as $index => $image) {
                if ($image['Id'] == $currentImageId) {
                    $previousIndex = ($index - 1 + count($images)) % count($images);
                    $previousImage = $images[$previousIndex];
                    break;
                }
            }
        }
        
        if (!$previousImage) {
            $previousImage = $images[count($images) - 1];
        }
        
        echo json_encode([
            'success' => true,
            'image' => $previousImage,
            'total' => count($images)
        ]);
        exit;
    }
}


