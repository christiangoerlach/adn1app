<?php
header('Content-Type: application/json');

require_once 'db.php'; // DB-Verbindung

$sql = "SELECT [FileName] FROM [dbo].[ImageRegistry]";
$stmt = $conn->query($sql);

$imageUrls = [];
$blobBaseUrl = "https://cgml15199447121.blob.core.windows.net/adntest/"; // anpassen falls nötig

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fileName = $row['FileName'];
    $imageUrls[] = $blobBaseUrl . rawurlencode($fileName);
}

echo json_encode($imageUrls, JSON_UNESCAPED_SLASHES);
?>
