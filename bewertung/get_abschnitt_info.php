<?php
header('Content-Type: application/json');

try {
    // Datenbankverbindung
    require_once '../config/database.php';
    
    // Session starten falls noch nicht gestartet
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Bild-ID aus GET-Parameter holen
    $bildId = $_GET['bildId'] ?? null;
    
    if (!$bildId) {
        throw new Exception('Bild-ID fehlt');
    }
    
    // SQL-Abfrage: Abschnittsinformationen für das Bild holen
    $sql = "SELECT a.abschnittname 
            FROM [dbo].[bilder] b
            LEFT JOIN [dbo].[abschnitte] a ON b.[abschnitte-id] = a.[Id]
            WHERE b.[Id] = :bild_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':bild_id', (int)$bildId, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'abschnittname' => $result['abschnittname']
        ]);
    } else {
        echo json_encode([
            'abschnittname' => null
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
