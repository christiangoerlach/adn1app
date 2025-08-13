<?php
// Session starten, falls noch nicht gestartet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php'; // Falls nötig, Pfad anpassen

$aktuellesProjekt = '';
$imageCount = null;

// Prüfen, ob eine Projekt-ID vorhanden ist
if (!empty($_SESSION['PROJEKT_ID'])) {

    // Projektname abfragen
    try {
        $stmt = $conn->prepare("SELECT Projektname FROM [dbo].[projects] WHERE Id = ?");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $aktuellesProjekt = $row['Projektname'];
        } else {
            $aktuellesProjekt = '(Projekt nicht gefunden)';
        }
    } catch (PDOException $e) {
        $aktuellesProjekt = 'Fehler beim Laden des Projekts: ' . htmlspecialchars($e->getMessage());
    }

    // Bildanzahl abfragen
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM [dbo].[bilder] WHERE [projects-id] = ?");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $imageCount = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $imageCount = 'Fehler: ' . htmlspecialchars($e->getMessage());
    }

} else {
    $aktuellesProjekt = null; // kein Projekt ausgewählt
}
?>

<!-- Anzeige -->
<?php if (!empty($_SESSION['PROJEKT_ID']) && $aktuellesProjekt): ?>
    <p>Aktuelles Projekt: <strong><?= htmlspecialchars($aktuellesProjekt) ?></strong></p>
    <p>Anzahl Bilder: <strong><?= is_numeric($imageCount) ? htmlspecialchars($imageCount) : $imageCount ?></strong></p>
<?php elseif (is_string($aktuellesProjekt) && !empty($aktuellesProjekt)): ?>
    <p><?= $aktuellesProjekt ?></p>
<?php else: ?>
    <p>Kein Projekt ausgewählt.</p>
<?php endif; ?>

<!-- Button immer unten -->
<?php if (!empty($_SESSION['PROJEKT_ID'])): ?>
    <div style="margin-top:20px;">
        <a href="bewertung/bewertung.php" class="button-link">Zur Bewertung</a>
    </div>
<?php endif; ?>
