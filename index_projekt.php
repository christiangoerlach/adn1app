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
