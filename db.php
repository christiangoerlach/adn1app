23:42

<?php
$env = parse_ini_file(__DIR__ . '/.env');

$serverName = $env['DB_SERVER'];
$connectionOptions = [
    "Database" => $env['DB_NAME'],
    "Uid" => $env['DB_USER'],
    "PWD" => $env['DB_PASS'],
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
$sql = "SELECT TOP 10 * FROM [dbo].[ImageRegistry]";
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
