<?php
session_start();

// Prüfen ob ein Projekt ausgewählt ist
if (!isset($_SESSION['PROJEKT_ID'])) {
    header('Location: ../index.php');
    exit;
}

// Parameter aus URL holen
$filter = $_GET['filter'] ?? 'all';
$wert = $_GET['wert'] ?? '';
$abschnittId = $_GET['abschnittId'] ?? '';

// Datenbankverbindung
require_once '../config/database.php';

// Benutzername aus Session oder Server-Variablen
$username = 'Gast';
if (!empty($_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"])) {
    $useremail = $_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"];
    $username = explode('@', $useremail)[0];
    $username = ucwords(str_replace('.', ' ', $username));
} elseif (isset($_SERVER['LOGON_USER'])) {
    $username = $_SERVER['LOGON_USER'];
} elseif (isset($_SESSION['USER_NAME'])) {
    $username = $_SESSION['USER_NAME'];
}

// Projektname laden
$projektname = 'Unbekanntes Projekt';
try {
    $stmt = $conn->prepare("SELECT Projektname FROM [dbo].[projects] WHERE Id = ?");
    $stmt->execute([$_SESSION['PROJEKT_ID']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $projektname = $result['Projektname'];
    }
} catch (PDOException $e) {
    error_log('Fehler beim Laden des Projektnamens: ' . $e->getMessage());
}

// Aktuellen Abschnitt laden
$currentAbschnitt = null;
$abschnittsname = 'Unbekannt';
$allAbschnitte = [];

try {
    if ($abschnittId) {
        // Spezifischen Abschnitt laden
        $stmt = $conn->prepare("SELECT [Id], [abschnittname] FROM [dbo].[abschnitte] WHERE [Id] = ? AND [projects-id] = ?");
        $stmt->execute([$abschnittId, $_SESSION['PROJEKT_ID']]);
        $currentAbschnitt = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($currentAbschnitt) {
            $abschnittsname = $currentAbschnitt['abschnittname'];
        }
    } elseif ($filter === 'straßenabschnitte' && $wert !== '') {
        // Ersten Abschnitt mit dem gewünschten Zustand laden
        if ($wert >= 1 && $wert <= 6) {
            $stmt = $conn->prepare("SELECT [Id], [abschnittname] FROM [dbo].[abschnitte] WHERE [projects-id] = ? AND [strasse] = ? ORDER BY [Id] LIMIT 1");
            $stmt->execute([$_SESSION['PROJEKT_ID'], (int)$wert]);
        } elseif ($wert === 0) {
            $stmt = $conn->prepare("SELECT [Id], [abschnittname] FROM [dbo].[abschnitte] WHERE [projects-id] = ? AND [strasse] IS NULL ORDER BY [Id] LIMIT 1");
            $stmt->execute([$_SESSION['PROJEKT_ID']]);
        }
        
        $currentAbschnitt = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($currentAbschnitt) {
            $abschnittsname = $currentAbschnitt['abschnittname'];
            $abschnittId = $currentAbschnitt['Id'];
        }
    }
    
    // Alle Abschnitte für Navigation laden
    $stmt = $conn->prepare("SELECT [Id], [abschnittname] FROM [dbo].[abschnitte] WHERE [projects-id] = ? ORDER BY [Id]");
    $stmt->execute([$_SESSION['PROJEKT_ID']]);
    $allAbschnitte = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log('Fehler beim Laden der Abschnitte: ' . $e->getMessage());
}

// Bilder für den aktuellen Abschnitt laden
$bilder = [];
if ($currentAbschnitt) {
    try {
        $sql = "SELECT [Id], [FileName] 
                FROM [dbo].[bilder] 
                WHERE [projects-id] = ? AND [abschnitte-id] = ?
                ORDER BY [Id]";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_SESSION['PROJEKT_ID'], $currentAbschnitt['Id']]);
        $bilder = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log('Fehler beim Laden der Bilder: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>ADN StraßenWeb - Abschnittsbewertung</title>
    <link rel="icon" href="https://adn-consulting.de/sites/default/files/favicon-96x96.png" type="image/png" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { 
            margin: 0;
            font-family: 'Open Sans', sans-serif; 
            background-color: #f4f4f4;
        }
        
        .top-bar {
            height: 4px;
            background: linear-gradient(90deg, #007bff, #0056b3);
            width: 100%;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            min-height: 40px;
        }
        
        .header-left {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .back-button {
            cursor: pointer;
            font-size: 1.5rem;
            color: #007bff;
            padding: 8px;
            border-radius: 5px;
            transition: all 0.3s ease;
            user-select: none;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-button:hover {
            background: #e3f2fd;
            color: #0056b3;
            transform: scale(1.1);
        }
        
        .header-left h1 {
            margin: 0;
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .header-center {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .header-center .nav-buttons {
            margin: 0;
        }
        
        .header-center button {
            font-size: 1.2rem;
            padding: 8px 16px;
            margin: 0 5px;
        }
        
        .header-center #counter {
            margin: 0 10px;
            font-weight: 600;
            color: #666;
            font-size: 1.2rem;
        }
        
        .header-right {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            font-weight: 600;
            color: #007bff;
            font-size: 1.2rem;
        }
        
        .content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .abschnitt-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .abschnitt-info h2 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.3rem;
        }
        
        .abschnitt-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .bilder-table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .bilder-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .bilder-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        .bilder-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .bilder-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .bilder-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .bild-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        
        .bild-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
        
        button { 
            font-size: 1.2rem; 
            padding: 10px 20px; 
            margin: 0 10px; 
            cursor: pointer;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }
        
        button:disabled {
            opacity: 0.5;
            cursor: default;
        }
        
        button:hover:not(:disabled) {
            background: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <div class="header-left">
        <a href="../index.php" class="back-button" title="Zurück zur Hauptseite">←</a>
        <h1><?= htmlspecialchars($projektname) ?>: <?= htmlspecialchars($abschnittsname) ?></h1>
    </div>
    <div class="header-center">
        <div class="nav-buttons">
            <button id="prevBtn" disabled>← Zurück</button>
            <span id="counter">0 / 0</span>
            <button id="nextBtn" disabled>Vor →</button>
        </div>
    </div>
    <div class="header-right">
        <span><?= htmlspecialchars($username) ?></span>
    </div>
</div>

<div class="content">
    <div class="abschnitt-info">
        <p><strong>Projekt:</strong> <?= htmlspecialchars($projektname) ?></p>
        <p><strong>Abschnitt:</strong> <?= htmlspecialchars($abschnittsname) ?></p>
        <p><strong>Anzahl Bilder:</strong> <?= count($bilder) ?></p>
    </div>
    
    <div class="bilder-table-container">
        <table class="bilder-table">
            <thead>
                <tr>
                    <th>Bild-ID</th>
                    <th>Dateiname</th>
                    <th>Aktion</th>
                </tr>
            </thead>
            <tbody id="bilder-table-body">
                <?php if (empty($bilder)): ?>
                    <tr>
                        <td colspan="3" class="no-data">Keine Bilder gefunden</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bilder as $bild): ?>
                        <tr>
                            <td><?= htmlspecialchars($bild['Id']) ?></td>
                            <td><?= htmlspecialchars($bild['FileName']) ?></td>
                            <td>
                                <a href="bewertung.php?bildId=<?= htmlspecialchars($bild['Id']) ?>" class="bild-link">
                                    Zur Bewertung
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
let bilder = <?= json_encode($bilder) ?>;
let allAbschnitte = <?= json_encode($allAbschnitte) ?>;
let currentAbschnittId = <?= json_encode($abschnittId) ?>;
let currentIndex = 0;

// Aktuellen Abschnittsindex finden
let currentAbschnittIndex = -1;
if (currentAbschnittId && allAbschnitte.length > 0) {
    currentAbschnittIndex = allAbschnitte.findIndex(abschnitt => abschnitt.Id == currentAbschnittId);
}

function updateCounter() {
    if (allAbschnitte.length === 0) {
        document.getElementById('counter').textContent = '0 / 0';
        document.getElementById('prevBtn').disabled = true;
        document.getElementById('nextBtn').disabled = true;
        return;
    }

    document.getElementById('counter').textContent = `${currentAbschnittIndex + 1} / ${allAbschnitte.length}`;
    
    // Enable/disable buttons accordingly
    document.getElementById('prevBtn').disabled = currentAbschnittIndex <= 0;
    document.getElementById('nextBtn').disabled = currentAbschnittIndex >= allAbschnitte.length - 1;
}

function navigateToAbschnitt(direction) {
    if (direction === 'prev' && currentAbschnittIndex > 0) {
        currentAbschnittIndex--;
    } else if (direction === 'next' && currentAbschnittIndex < allAbschnitte.length - 1) {
        currentAbschnittIndex++;
    } else {
        return;
    }
    
    // Zur neuen Abschnittsseite navigieren
    const newAbschnittId = allAbschnitte[currentAbschnittIndex].Id;
    window.location.href = `abschnitt-bewertung.php?abschnittId=${newAbschnittId}`;
}

document.getElementById('prevBtn').addEventListener('click', () => {
    navigateToAbschnitt('prev');
});

document.getElementById('nextBtn').addEventListener('click', () => {
    navigateToAbschnitt('next');
});

// Initial update
updateCounter();
</script>

</body>
</html>
