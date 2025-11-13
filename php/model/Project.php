<?php
/**
 * Project Model
 * Verwaltet alle Datenbankoperationen für Projekte
 */

class Project {
    private $conn;
    
    public function __construct() {
        // Versuche globale Verbindung zu nutzen
        global $conn;
        
        // Falls globale Verbindung nicht existiert, nutze database.php
        if (!isset($conn) || $conn === null) {
            if (file_exists(__DIR__ . '/../../config/database.php')) {
                require_once __DIR__ . '/../../config/database.php';
                $conn = getDatabaseConnection();
            }
        }
        
        $this->conn = $conn;
    }
    
    /**
     * Holt alle Projekte aus der Datenbank
     * @return array Array mit allen Projekten
     */
    public function getAllProjects() {
        try {
            if ($this->conn === null) {
                error_log("Fehler: Datenbankverbindung ist null");
                return [];
            }
            // CONVERT zu VARCHAR für Sortierung, da Projektname möglicherweise TEXT/NTEXT ist
            $stmt = $this->conn->query("SELECT Id, Projektname FROM [dbo].[projects] ORDER BY CONVERT(VARCHAR(MAX), Projektname)");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($result)) {
                error_log("Warnung: Keine Projekte in der Datenbank gefunden");
            }
            return $result;
        } catch (PDOException $e) {
            // Fallback: Ohne Sortierung versuchen
            try {
                error_log("Fehler mit Sortierung, versuche ohne Sortierung: " . $e->getMessage());
                $stmt = $this->conn->query("SELECT Id, Projektname FROM [dbo].[projects] ORDER BY Id");
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Manuell nach Namen sortieren in PHP
                usort($result, function($a, $b) {
                    return strcmp($a['Projektname'], $b['Projektname']);
                });
                return $result;
            } catch (PDOException $e2) {
                error_log("Fehler beim Abrufen der Projekte: " . $e2->getMessage());
                return [];
            }
        }
    }
    
    /**
     * Holt ein spezifisches Projekt nach ID
     * @param int $id Projekt-ID
     * @return array|null Projektdaten oder null
     */
    public function getProjectById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM [dbo].[projects] WHERE Id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Fehler beim Abrufen des Projekts: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Holt den Bilder-Container für ein Projekt
     * @param int $id Projekt-ID
     * @return string|null Container-Name oder null
     */
    public function getBilderContainer($id) {
        try {
            $stmt = $this->conn->prepare("SELECT BilderContainer FROM [dbo].[projects] WHERE Id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['BilderContainer'] : null;
        } catch (PDOException $e) {
            error_log("Fehler beim Abrufen des BilderContainers: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Prüft ob ein Projekt existiert
     * @param int $id Projekt-ID
     * @return bool True wenn Projekt existiert
     */
    public function projectExists($id) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM [dbo].[projects] WHERE Id = ?");
            $stmt->execute([$id]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Fehler beim Prüfen der Projektexistenz: " . $e->getMessage());
            return false;
        }
    }
}


