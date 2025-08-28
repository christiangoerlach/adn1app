<?php
/**
 * Image Model
 * Verwaltet alle Datenbankoperationen für Bilder
 */

class Image {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Zählt die Bilder für ein Projekt
     * @param int $projectId Projekt-ID
     * @return int Anzahl der Bilder
     */
    public function getImageCountByProject($projectId) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM [dbo].[bilder] WHERE [projects-id] = ?");
            $stmt->execute([$projectId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Fehler beim Zählen der Bilder: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Holt alle Bilder für ein Projekt
     * @param int $projectId Projekt-ID
     * @return array Array mit allen Bildern
     */
    public function getImagesByProject($projectId) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM [dbo].[bilder] WHERE [projects-id] = ? ORDER BY Id");
            $stmt->execute([$projectId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Fehler beim Abrufen der Bilder: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Holt ein spezifisches Bild nach ID
     * @param int $id Bild-ID
     * @return array|null Bilddaten oder null
     */
    public function getImageById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM [dbo].[bilder] WHERE Id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Fehler beim Abrufen des Bildes: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Aktualisiert die Bewertung eines Bildes
     * @param int $id Bild-ID
     * @param string $rating Bewertung
     * @return bool True wenn erfolgreich
     */
    public function updateRating($id, $rating) {
        try {
            $stmt = $this->conn->prepare("UPDATE [dbo].[bilder] SET Bewertung = ? WHERE Id = ?");
            return $stmt->execute([$rating, $id]);
        } catch (PDOException $e) {
            error_log("Fehler beim Aktualisieren der Bewertung: " . $e->getMessage());
            return false;
        }
    }
}


