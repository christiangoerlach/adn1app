<?php
session_start(); // Session starten

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB-Verbindung laden
require_once 'db.php'; // stellt $conn bereit

// Projekte abrufen
$options = [];
try {
    $stmt = $conn->query("SELECT Id, Projektname FROM [dbo].[projects]");
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

    // Projektinformationen abrufen
    try {
        $stmt = $conn->prepare("SELECT BilderContainer FROM [dbo].[projects] WHERE Id = ?");
        $stmt->execute([$projektId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && isset($row['BilderContainer'])) {
            $containerName = $row['BilderContainer'];

            // Session-Variablen setzen
            $_SESSION['PROJEKT_ID'] = $projektId;
            $_SESSION['AZURE_STORAGE_CONTAINER_NAME'] = $containerName;

            // Ausgabe
            echo "<p>Session-Variable <strong>PROJEKT_ID</strong> wurde auf <strong>" . htmlspecialchars($_SESSION['PROJEKT_ID']) . "</strong> gesetzt.</p>";
            echo "<p>Session-Variable <strong>AZURE_STORAGE_CONTAINER_NAME</strong> wurde auf <strong>" . htmlspecialchars($_SESSION['AZURE_STORAGE_CONTAINER_NAME']) . "</strong> gesetzt.</p>";
        } else {
            echo "<p>Fehler: Kein BilderContainer für das ausgewählte Projekt gefunden.</p>";
        }
    } catch (PDOException $e) {
        echo "DB-Fehler beim Abrufen des BilderContainers: " . $e->getMessage();
    }
}
?>
