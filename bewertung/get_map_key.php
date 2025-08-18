<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Azure Maps Key aus .env holen
$azureMapsKey = $_ENV['AZURE_MAPS_KEY'] ?? '';

if (empty($azureMapsKey)) {
    http_response_code(500);
    echo json_encode(['error' => 'Azure Maps Key nicht gefunden']);
    exit;
}

echo json_encode(['key' => $azureMapsKey]);
?>
