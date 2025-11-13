<?php
/**
 * Home Controller
 * Zeigt die Hauptübersichtsseite mit Auswahlmöglichkeiten
 */

// Konfiguration ist bereits in public/index.php geladen
// APP_NAME sollte verfügbar sein

class HomeController {
    
    /**
     * Zeigt die Übersichtsseite
     */
    public function index() {
        // Session starten
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Benutzerinformationen abrufen
        $userInfo = $this->getUserInfo();
        
        // View laden
        require_once __DIR__ . '/../view/home/index.php';
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

