23:48

<?php

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$serverName = $_ENV['DB_SERVER'];
$connectionOptions = [
    "Database" => $_ENV['DB_NAME'],
    "Uid" => $_ENV['DB_USER'],
    "PWD" => $_ENV['DB_PASS'],
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
];

// Verbindung aufbauen
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

echo "Verbindung erfolgreich!<br>";

// Beispielabfrage
$sql = "SELECT * FROM [dbo].[ImageRegistry]";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Daten ausgeben
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo htmlspecialchars($row['spaltenname']) . "<br>";
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
