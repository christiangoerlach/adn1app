<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'bildId' => 1,
    'strasse' => 3,
];
require __DIR__ . '/save_bewertung.php';
