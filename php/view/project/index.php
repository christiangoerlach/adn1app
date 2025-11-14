<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?></title>
    <link rel="icon" href="https://adn-consulting.de/sites/default/files/favicon-96x96.png" type="image/png" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <div class="header-left">
        <a href="/index.php" style="display: inline-block; text-decoration: none;">
            <img src="https://adn-consulting.de/sites/default/files/Logo-ADN_0_0.jpg" alt="ADN Logo" style="cursor: pointer;">
        </a>
    </div>
    <div class="header-title"><?= APP_NAME ?></div>
    <div class="header-right">
        <span><?= htmlspecialchars($userInfo['username']) ?></span>
        <?php if ($userInfo['useremail']): ?>
            <span><?= htmlspecialchars($userInfo['useremail']) ?></span>
        <?php endif; ?>
    </div>
</div>

<div class="content">
    <div class="project-select">
        <select name="auswahl" id="projekt-auswahl">
            <option value="">-- Projekt wählen --</option>
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $project): ?>
                    <option value="<?= htmlspecialchars($project['Id']) ?>" 
                            <?= (isset($_SESSION['PROJEKT_ID']) && $_SESSION['PROJEKT_ID'] == $project['Id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($project['Projektname']) ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="">-- Keine Projekte gefunden --</option>
            <?php endif; ?>
        </select>
    </div>

    <div id="projekt-content">
        <?php 
        // Inkludiere index_projekt.php für die Statistiken und detaillierte Ansicht
        if (file_exists(__DIR__ . '/../../../index_projekt.php')) {
            include __DIR__ . '/../../../index_projekt.php';
        } else {
            // Fallback wenn index_projekt.php nicht existiert
            if ($currentProject): ?>
                <p>Aktuelles Projekt: <strong><?= htmlspecialchars($currentProject['Projektname']) ?></strong></p>
                <p>Anzahl Bilder: <strong><?= $imageCount ?></strong></p>
                
                <div style="margin-top:20px;">
                    <a href="/index.php?path=bewertung" class="button-link">Zur Bewertung</a>
                </div>
            <?php else: ?>
                <p>Kein Projekt ausgewählt.</p>
            <?php endif;
        }
        ?>
    </div>
</div>

<script src="/js/project.js"></script>
<script>
// Zusätzliche Initialisierung nach dem Laden von index_projekt.php
// Falls index_projekt.php asynchron geladen wird
if (typeof initProjectSelect === 'function') {
    initProjectSelect();
}
</script>

</body>
</html>


