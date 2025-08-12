<?php
$serverName = "tcp:adndb.database.windows.net,1433";
$connectionOptions = [
    "Database" => "adn_db",
    "Uid" => "adn_db", // oft im Format: benutzer@servername
    "PWD" => "Ericgmetro1!",
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
$sql = "SELECT TOP 10 * FROM deine_tabelle";
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
