<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


require_once 'db.php';

// Prüfen, ob ein aktuelles Projekt gesetzt ist
$aktuellesProjekt = '';
if (isset($_SESSION['PROJEKT_ID'])) {
    try {
        $stmt = $conn->prepare("SELECT Projektname FROM [dbo].[projects] WHERE Id = ?");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $aktuellesProjekt = $row['Projektname'];
        }
    } catch (PDOException $e) {
        echo "Fehler beim Laden des Projekts: " . $e->getMessage();
        exit;
    }
}
?>

<?php if ($aktuellesProjekt): ?>
    <p>Aktuelles Projekt: <strong><?= htmlspecialchars($aktuellesProjekt) ?></strong></p>
<?php else: ?>
    <p>Kein Projekt ausgewählt.</p>
<?php endif; ?>

<a href="bewertung/bewertung.php" class="button-link">Zur Bewertung</a>

<?php
// Session starten, falls noch nicht gestartet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php'; // Pfad anpassen falls notwendig

// Anzahl der Einträge aus dbo.ImageRegistry abfragen
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM [dbo].[ImageRegistry]");
    $imageCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    $imageCount = "Fehler: " . $e->getMessage();
}
?>

<h2>Anzahl der Einträge in ImageRegistry</h2>
<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>Tabellenname</th>
            <th>Anzahl Einträge</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>dbo.ImageRegistry</td>
            <td><?= is_numeric($imageCount) ? htmlspecialchars($imageCount) : $imageCount ?></td>
        </tr>
    </tbody>
</table>
