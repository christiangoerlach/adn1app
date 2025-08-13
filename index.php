1304

<?php
// DB-Verbindung laden
require_once __DIR__ . '/db.php'; // Pfad anpassen!

// Projekte abrufen
$options = [];
try {
    $stmt = $pdo->query("SELECT Id, Projektname FROM [dbo].[projects] ORDER BY Projektname ASC");
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "DB-Fehler: " . $e->getMessage();
}
?>

<!-- Formular -->
<form method="post" action="">
    <select name="auswahl">
        <option value="">-- Bitte wählen --</option>
        <?php foreach ($options as $opt): ?>
            <option value="<?= htmlspecialchars($opt['Id']) ?>"
                <?php if (isset($_POST['auswahl']) && $_POST['auswahl'] == $opt['Id']) echo 'selected'; ?>>
                <?= htmlspecialchars($opt['Projektname']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Auswählen</button>
</form>

<?php
// Auswahl verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['auswahl'])) {
    $projektId = $_POST['auswahl'];

    // Umgebungvariable setzen (nur für die Laufzeit)
    putenv("PROJEKT_ID={$projektId}");
    $meineVariable = getenv("PROJEKT_ID");

    echo "<p>Umgebungsvariable PROJEKT_ID wurde auf <strong>" . htmlspecialchars($meineVariable) . "</strong> gesetzt.</p>";
}
?>
