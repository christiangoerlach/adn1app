<?php
require_once __DIR__ . '/../db.php';

try {
    echo "Tabellen in DB:\n";
    foreach ($conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'") as $r) {
        echo "- ".$r['TABLE_NAME']."\n";
    }

    echo "\nStruktur von bewertung:\n";
    $columns = $conn->query("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'bewertung'");
    foreach ($columns as $c) {
        echo "- ".$c['COLUMN_NAME']." (".$c['DATA_TYPE'].")\n";
    }

    echo "\nBeispielwerte (TOP 5) aus bewertung:\n";
    foreach ($conn->query("SELECT TOP 5 * FROM bewertung") as $row) {
        print_r($row);
    }
} catch (PDOException $e) {
    echo 'Fehler: ' . $e->getMessage() . "\n";
}
