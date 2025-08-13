<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

// Benutzername aus Session
$username = $_SESSION['username'] ?? ($_SESSION['user_email'] ?? 'Gast');

// Projekte abrufen
$options = [];
try {
    $stmt = $conn->query("SELECT Id, Projektname FROM [dbo].[projects]");
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "DB-Fehler: " . $e->getMessage();
}

// Projektwechsel verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auswahl'])) {
    $projektId = $_POST['auswahl'];
    try {
        $stmt = $conn->prepare("SELECT BilderContainer FROM [dbo].[projects] WHERE Id = ?");
        $stmt->execute([$projektId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && isset($row['BilderContainer'])) {
            $_SESSION['PROJEKT_ID'] = $projektId;
            $_SESSION['AZURE_STORAGE_CONTAINER_NAME'] = $row['BilderContainer'];
        }
    } catch (PDOException $e) {
        echo "DB-Fehler beim Abrufen des BilderContainers: " . $e->getMessage();
    }
}

// Aktuelles Projekt bestimmen
$aktuellesProjekt = '';
if (isset($_SESSION['PROJEKT_ID'])) {
    foreach ($options as $opt) {
        if ($opt['Id'] == $_SESSION['PROJEKT_ID']) {
            $aktuellesProjekt = $opt['Projektname'];
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>ADN Web Portal</title>
    <style>
        /* ... dein CSS-Code ... */
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-left">ADN Web</div>
    <div class="header-center">
        <form method="post" action="">
            <select name="auswahl" onchange="this.form.submit()">
                <option value="">-- Projekt wählen --</option>
                <?php foreach ($options as $opt): ?>
                    <option value="<?= htmlspecialchars($opt['Id']) ?>"
                        <?= (isset($_SESSION['PROJEKT_ID']) && $_SESSION['PROJEKT_ID'] == $opt['Id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($opt['Projektname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="header-right"><?= htmlspecialchars($username) ?></div>
</div>

<!-- Inhalt -->
<div class="content">
    <?php if ($aktuellesProjekt): ?>
        <p>Aktuelles Projekt: <strong><?= htmlspecialchars($aktuellesProjekt) ?></strong></p>
    <?php else: ?>
        <p>Kein Projekt ausgewählt.</p>
    <?php endif; ?>

    <a href="bewertung/bewertung.php" class="button-link">Zur Bewertung</a>
</div>

</body>
</html>
