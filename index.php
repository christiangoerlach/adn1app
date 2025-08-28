<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

// Benutzername und E-Mail aus Azure Authentication
$username = 'Gast';
$useremail = '';

if (!empty($_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"])) {
    $useremail = $_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"];
    $username = explode('@', $useremail)[0];
    $username = ucwords(str_replace('.', ' ', $username));
} elseif (isset($_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL'])) {
    $principal = json_decode(base64_decode($_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL']), true);
    if (isset($principal['userDetails'])) {
        $useremail = $principal['userDetails'];
    }
    if (isset($principal['name'])) {
        $username = $principal['name'];
    }
} elseif (isset($_SERVER['LOGON_USER'])) {
    $username = $_SERVER['LOGON_USER'];
} elseif (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}

// Projekte abrufen
$options = [];
try {
    $stmt = $conn->query("SELECT Id, Projektname FROM [dbo].[projects]");
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "DB-Fehler: " . $e->getMessage();
}

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
    exit; // Wichtig! Bei POST nur Session setzen, JS lädt neuen Content
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>ADN Web Portal</title>
    <link rel="icon" href="https://adn-consulting.de/sites/default/files/favicon-96x96.png" type="image/png" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Open Sans', sans-serif; background-color: #f4f4f4; }
        .top-bar { background-color: #003366; height: 8px; width: 100%; }
        .header { background-color: white; display: flex; align-items: center; justify-content: space-between; padding: 15px 30px; border-bottom: 1px solid #ccc; position: relative; }
        .header-left img { height: 50px; }
        .header-title { position: absolute; left: 50%; transform: translateX(-50%); font-size: 24px; font-weight: 600; color: #000; }
        .header-right { font-size: 14px; color: #333; text-align: right; }
        .header-right span { display: block; }
        .content { padding: 30px; }
        .project-select { margin-bottom: 20px; }
        .project-select select { font-size: 14px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; background-color: #f9f9f9; }
        a.button-link { display: inline-block; background-color: #0078D4; color: white; padding: 10px 15px; border-radius: 4px; font-weight: 600; text-decoration: none; }
        a.button-link:hover { background-color: #005a9e; }
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <div class="header-left">
        <img src="https://adn-consulting.de/sites/default/files/Logo-ADN_0_0.jpg" alt="ADN Logo">
    </div>
    <div class="header-title">A.D.N. StraßenWeb</div>
    <div class="header-right">
        <span><?= htmlspecialchars($username) ?></span>
        <?php if ($useremail): ?>
            <span><?= htmlspecialchars($useremail) ?></span>
        <?php endif; ?>
    </div>
</div>

<div class="content">
    <div class="project-select">
        <select name="auswahl" id="projekt-auswahl">
            <option value="">-- Projekt wählen --</option>
            <?php foreach ($options as $opt): ?>
                <option value="<?= htmlspecialchars($opt['Id']) ?>" <?= (isset($_SESSION['PROJEKT_ID']) && $_SESSION['PROJEKT_ID'] == $opt['Id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opt['Projektname']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="projekt-content">
        <?php include 'index_projekt.php'; ?>
    </div>
</div>

<script>
document.getElementById('projekt-auswahl').addEventListener('change', function() {
    let projektId = this.value;
    let formData = new FormData();
    formData.append('auswahl', projektId);

    fetch('', {
        method: 'POST',
        body: formData
    }).then(() => {
        // Nach Setzen der Session den Projektbereich neu laden
        fetch('index_projekt.php')
            .then(response => response.text())
            .then(html => {
                document.getElementById('projekt-content').innerHTML = html;
            });
    });
});
</script>

</body>
</html>


