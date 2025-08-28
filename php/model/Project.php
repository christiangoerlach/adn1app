<?php
/**
 * Project Model
 * Verwaltet alle Datenbankoperationen für Projekte
 */

class Project {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Holt alle Projekte aus der Datenbank
     * @return array Array mit allen Projekten
     */
    public function getAllProjects() {
        try {
            $stmt = $this->conn->query("SELECT Id, Projektname FROM [dbo].[projects] ORDER BY Projektname");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Fehler beim Abrufen der Projekte: " . $e->getMessage());
            return [];
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


