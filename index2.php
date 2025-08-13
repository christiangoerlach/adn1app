
0753
<?php
header('Content-Type: application/json');

// Verbindung zur Datenbank herstellen
require_once 'db.php';

// SQL nur die benötigte Spalte holen
$sql = "SELECT [FileName] FROM [dbo].[ImageRegistry]";
$stmt = $conn->query($sql);

// Array für URLs vorbereiten
$imageUrls = [];

// Azure Blob Storage Basis-URL (bitte anpassen)
$blobBaseUrl = "https://cgml1519944712.blob.core.windows.net/adntest/";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fileName = $row['FileName'];

    // URL zusammensetzen
    $imageUrls[] = $blobBaseUrl . rawurlencode($fileName);
}

// JSON ausgeben
echo json_encode($imageUrls);
?>
