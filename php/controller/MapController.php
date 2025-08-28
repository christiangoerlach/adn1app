<?php
/**
 * Map Controller
 * Steuert die Azure Maps-Funktionalität
 */

class MapController {
    
    /**
     * Zeigt die Kartenansicht
     */
    public function index() {
        // Azure Maps Key aus der Konfiguration holen
        $azureMapsKey = AZURE_MAPS_KEY;
        
        if (empty($azureMapsKey)) {
            die('Azure Maps Key nicht konfiguriert');
        }
        
        require_once __DIR__ . '/../view/map/index.php';
    }
    
    /**
     * Zeigt die alte Kartenansicht
     */
    public function showOld() {
        $azureMapsKey = AZURE_MAPS_KEY;
        
        if (empty($azureMapsKey)) {
            die('Azure Maps Key nicht konfiguriert');
        }
        
        require_once __DIR__ . '/../view/map/old.php';
    }
}


