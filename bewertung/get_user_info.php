<?php
session_start();
header('Content-Type: application/json');

/**
 * Holt Benutzerinformationen aus verschiedenen Quellen
 * @return array Benutzerinformationen
 */
function getUserInfo() {
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

// Benutzerinformationen abrufen und zurückgeben
$userInfo = getUserInfo();

// Benutzer in Session speichern für Logging
$_SESSION['USER_NAME'] = $userInfo['username'];
$_SESSION['USER_EMAIL'] = $userInfo['useremail'];

echo json_encode($userInfo);
?>
