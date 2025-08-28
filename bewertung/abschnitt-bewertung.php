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

// Bilder mit Bewertungsdaten für den aktuellen Abschnitt laden
$bilder = [];
$abschnittBewertung = null;

if ($currentAbschnitt) {
    try {
        // Abschnittsbewertung laden
        $sqlBewertung = "SELECT [strasse], [gehweg_links], [gehweg_rechts], 
                                [seitenstreifen_links], [seitenstreifen_rechts],
                                [review], [schaden], [text]
                         FROM [dbo].[abschnitte_bewertung] 
                         WHERE [abschnitte-id] = ?";
        
        $stmtBewertung = $conn->prepare($sqlBewertung);
        $stmtBewertung->execute([$currentAbschnitt['Id']]);
        $abschnittBewertung = $stmtBewertung->fetch(PDO::FETCH_ASSOC);
        
        // Bilder laden
        $sql = "SELECT b.[Id], b.[FileName], 
                       bew.[strasse], bew.[gehweg_links], bew.[gehweg_rechts], 
                       bew.[seitenstreifen_links], bew.[seitenstreifen_rechts],
                       bew.[review], bew.[schaden]
                FROM [dbo].[bilder] b
                LEFT JOIN [dbo].[bewertung] bew ON b.[Id] = bew.[bilder-id]
                WHERE b.[projects-id] = ? AND b.[abschnitte-id] = ?
                ORDER BY b.[Id]";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_SESSION['PROJEKT_ID'], $currentAbschnitt['Id']]);
        $bilder = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log('Fehler beim Laden der Daten: ' . $e->getMessage());
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
        
        .summary-section {
            margin-bottom: 30px;
        }
        
        .summary-section h2 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .summary-table-container {
            background: white;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .summary-table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
            table-layout: fixed;
        }
        
        .summary-table th:first-child,
        .summary-table td:first-child {
            width: 100px;
            text-align: left;
        }
        

        
        .summary-table th {
            background: #e9ecef;
            padding: 10px 8px;
            text-align: center;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        
        .summary-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.85rem;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        
        .summary-table td:first-child {
            text-align: left;
        }
        
        .summary-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .bilder-table-container h2 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .bilder-table-container {
            background: white;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .bilder-table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
            table-layout: fixed;
        }
        
        .bilder-table th:first-child,
        .bilder-table td:first-child {
            width: 100px;
        }
        

        
        .bilder-table th {
            background: #f8f9fa;
            padding: 10px 8px;
            text-align: center;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        
        .bilder-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.85rem;
            text-align: center;
        }
        
        .bilder-table td:first-child {
            text-align: left;
        }
        
        .bilder-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .bild-id-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }
        
        .bild-id-link:hover {
            color: #0056b3;
            text-decoration: underline;
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
        
        /* Log-Sektion Styles */
        .log-section {
            margin-bottom: 30px;
        }
        
        .collapsible-header {
            cursor: pointer;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .collapsible-header:hover {
            background: #e9ecef;
        }
        
        .toggle-icon {
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }
        
        .collapsible-content {
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 8px 8px;
            background: white;
        }
        
        .log-table-container {
            padding: 20px;
            overflow-x: auto;
        }
        
        .log-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        .log-table th {
            background: #f8f9fa;
            padding: 10px 8px;
            text-align: center;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }
        
        .log-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
            color: #333;
        }
        
        .log-table td:first-child {
            text-align: left;
        }
        
        .log-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        /* Notizen Textarea Styles */
        #abschnitt-notizen {
            resize: vertical;
            min-height: 60px;
        }
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <div class="header-left">
        <a href="../index.php" class="back-button" title="Zurück zur Hauptseite">←</a>
                        <h1><?= htmlspecialchars($projektname) ?>: <?= htmlspecialchars($abschnittsname) ?> (Anzahl der Bilder: <?= count($bilder) ?>)</h1>
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
    <div class="summary-section">
        <h2>Zusammenfassung</h2>
        <div class="summary-table-container">
            <table class="summary-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Straße</th>
                        <th>Gehweg Links</th>
                        <th>Gehweg Rechts</th>
                        <th>Seitenstreifen Links</th>
                        <th>Seitenstreifen Rechts</th>
                        <th>Review</th>
                        <th>Schaden</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Durchschnitt</strong></td>
                        <td><?php
                            $strasseValues = array_filter(array_column($bilder, 'strasse'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($strasseValues) > 0 ? number_format(array_sum($strasseValues) / count($strasseValues), 2) : '-';
                        ?></td>
                        <td><?php
                            $gehwegLinksValues = array_filter(array_column($bilder, 'gehweg_links'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($gehwegLinksValues) > 0 ? number_format(array_sum($gehwegLinksValues) / count($gehwegLinksValues), 2) : '-';
                        ?></td>
                        <td><?php
                            $gehwegRechtsValues = array_filter(array_column($bilder, 'gehweg_rechts'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($gehwegRechtsValues) > 0 ? number_format(array_sum($gehwegRechtsValues) / count($gehwegRechtsValues), 2) : '-';
                        ?></td>
                        <td><?php
                            $seitenstreifenLinksValues = array_filter(array_column($bilder, 'seitenstreifen_links'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($seitenstreifenLinksValues) > 0 ? number_format(array_sum($seitenstreifenLinksValues) / count($seitenstreifenLinksValues), 2) : '-';
                        ?></td>
                        <td><?php
                            $seitenstreifenRechtsValues = array_filter(array_column($bilder, 'seitenstreifen_rechts'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($seitenstreifenRechtsValues) > 0 ? number_format(array_sum($seitenstreifenRechtsValues) / count($seitenstreifenRechtsValues), 2) : '-';
                        ?></td>
                        <td><?php
                            $reviewCount = count(array_filter(array_column($bilder, 'review'), function($val) {
                                return $val == 1;
                            }));
                            echo $reviewCount;
                        ?></td>
                        <td><?php
                            $schadenCount = count(array_filter(array_column($bilder, 'schaden'), function($val) {
                                return $val == 1;
                            }));
                            echo $schadenCount;
                        ?></td>
                    </tr>
                    <tr>
                        <td><strong>Min.</strong></td>
                        <td><?php
                            $strasseValues = array_filter(array_column($bilder, 'strasse'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($strasseValues) > 0 ? min($strasseValues) : '-';
                        ?></td>
                        <td><?php
                            $gehwegLinksValues = array_filter(array_column($bilder, 'gehweg_links'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($gehwegLinksValues) > 0 ? min($gehwegLinksValues) : '-';
                        ?></td>
                        <td><?php
                            $gehwegRechtsValues = array_filter(array_column($bilder, 'gehweg_rechts'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($gehwegRechtsValues) > 0 ? min($gehwegRechtsValues) : '-';
                        ?></td>
                        <td><?php
                            $seitenstreifenLinksValues = array_filter(array_column($bilder, 'seitenstreifen_links'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($seitenstreifenLinksValues) > 0 ? min($seitenstreifenLinksValues) : '-';
                        ?></td>
                        <td><?php
                            $seitenstreifenRechtsValues = array_filter(array_column($bilder, 'seitenstreifen_rechts'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($seitenstreifenRechtsValues) > 0 ? min($seitenstreifenRechtsValues) : '-';
                        ?></td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td><strong>Max.</strong></td>
                        <td><?php
                            $strasseValues = array_filter(array_column($bilder, 'strasse'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($strasseValues) > 0 ? max($strasseValues) : '-';
                        ?></td>
                        <td><?php
                            $gehwegLinksValues = array_filter(array_column($bilder, 'gehweg_links'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($gehwegLinksValues) > 0 ? max($gehwegLinksValues) : '-';
                        ?></td>
                        <td><?php
                            $gehwegRechtsValues = array_filter(array_column($bilder, 'gehweg_rechts'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($gehwegRechtsValues) > 0 ? max($gehwegRechtsValues) : '-';
                        ?></td>
                        <td><?php
                            $seitenstreifenLinksValues = array_filter(array_column($bilder, 'seitenstreifen_links'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($seitenstreifenLinksValues) > 0 ? max($seitenstreifenLinksValues) : '-';
                        ?></td>
                        <td><?php
                            $seitenstreifenRechtsValues = array_filter(array_column($bilder, 'seitenstreifen_rechts'), function($val) {
                                return $val !== null && $val >= 1 && $val <= 6;
                            });
                            echo count($seitenstreifenRechtsValues) > 0 ? max($seitenstreifenRechtsValues) : '-';
                        ?></td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                                         <tr>
                         <td><strong>Bewertung:</strong></td>
                         <td>
                             <select class="bewertung-dropdown" data-field="strasse" style="width: 50px;">
                                 <option value="1" <?= ($abschnittBewertung && $abschnittBewertung['strasse'] == 1) ? 'selected' : '' ?>>1</option>
                                 <option value="2" <?= ($abschnittBewertung && $abschnittBewertung['strasse'] == 2) ? 'selected' : '' ?>>2</option>
                                 <option value="3" <?= ($abschnittBewertung && $abschnittBewertung['strasse'] == 3) ? 'selected' : '' ?>>3</option>
                                 <option value="4" <?= ($abschnittBewertung && $abschnittBewertung['strasse'] == 4) ? 'selected' : '' ?>>4</option>
                                 <option value="5" <?= ($abschnittBewertung && $abschnittBewertung['strasse'] == 5) ? 'selected' : '' ?>>5</option>
                                 <option value="6" <?= ($abschnittBewertung && $abschnittBewertung['strasse'] == 6) ? 'selected' : '' ?>>6</option>
                             </select>
                         </td>
                         <td>
                             <select class="bewertung-dropdown" data-field="gehweg_links" style="width: 50px;">
                                 <option value="1" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_links'] == 1) ? 'selected' : '' ?>>1</option>
                                 <option value="2" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_links'] == 2) ? 'selected' : '' ?>>2</option>
                                 <option value="3" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_links'] == 3) ? 'selected' : '' ?>>3</option>
                                 <option value="4" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_links'] == 4) ? 'selected' : '' ?>>4</option>
                                 <option value="5" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_links'] == 5) ? 'selected' : '' ?>>5</option>
                                 <option value="6" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_links'] == 6) ? 'selected' : '' ?>>6</option>
                                 <option value="0" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_links'] == 0) ? 'selected' : '' ?>>Noch nicht bewertet</option>
                                 <option value="9" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_links'] == 9) ? 'selected' : '' ?>>Bewertung ausgeschlossen</option>
                                 <option value="10" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_links'] == 10) ? 'selected' : '' ?>>Nicht vorhanden</option>
                                 <option value="11" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_links'] == 11) ? 'selected' : '' ?>>Wie Straße</option>
                             </select>
                         </td>
                         <td>
                             <select class="bewertung-dropdown" data-field="gehweg_rechts" style="width: 50px;">
                                 <option value="1" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_rechts'] == 1) ? 'selected' : '' ?>>1</option>
                                 <option value="2" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_rechts'] == 2) ? 'selected' : '' ?>>2</option>
                                 <option value="3" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_rechts'] == 3) ? 'selected' : '' ?>>3</option>
                                 <option value="4" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_rechts'] == 4) ? 'selected' : '' ?>>4</option>
                                 <option value="5" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_rechts'] == 5) ? 'selected' : '' ?>>5</option>
                                 <option value="6" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_rechts'] == 6) ? 'selected' : '' ?>>6</option>
                                 <option value="0" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_rechts'] == 0) ? 'selected' : '' ?>>Noch nicht bewertet</option>
                                 <option value="9" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_rechts'] == 9) ? 'selected' : '' ?>>Bewertung ausgeschlossen</option>
                                 <option value="10" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_rechts'] == 10) ? 'selected' : '' ?>>Nicht vorhanden</option>
                                 <option value="11" <?= ($abschnittBewertung && $abschnittBewertung['gehweg_rechts'] == 11) ? 'selected' : '' ?>>Wie Straße</option>
                             </select>
                         </td>
                         <td>
                             <select class="bewertung-dropdown" data-field="seitenstreifen_links" style="width: 50px;">
                                 <option value="1" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_links'] == 1) ? 'selected' : '' ?>>1</option>
                                 <option value="2" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_links'] == 2) ? 'selected' : '' ?>>2</option>
                                 <option value="3" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_links'] == 3) ? 'selected' : '' ?>>3</option>
                                 <option value="4" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_links'] == 4) ? 'selected' : '' ?>>4</option>
                                 <option value="5" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_links'] == 5) ? 'selected' : '' ?>>5</option>
                                 <option value="6" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_links'] == 6) ? 'selected' : '' ?>>6</option>
                                 <option value="0" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_links'] == 0) ? 'selected' : '' ?>>Noch nicht bewertet</option>
                                 <option value="9" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_links'] == 9) ? 'selected' : '' ?>>Bewertung ausgeschlossen</option>
                                 <option value="10" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_links'] == 10) ? 'selected' : '' ?>>Nicht vorhanden</option>
                                 <option value="11" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_links'] == 11) ? 'selected' : '' ?>>Wie Straße</option>
                             </select>
                         </td>
                         <td>
                             <select class="bewertung-dropdown" data-field="seitenstreifen_rechts" style="width: 50px;">
                                 <option value="1" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_rechts'] == 1) ? 'selected' : '' ?>>1</option>
                                 <option value="2" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_rechts'] == 2) ? 'selected' : '' ?>>2</option>
                                 <option value="3" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_rechts'] == 3) ? 'selected' : '' ?>>3</option>
                                 <option value="4" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_rechts'] == 4) ? 'selected' : '' ?>>4</option>
                                 <option value="5" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_rechts'] == 5) ? 'selected' : '' ?>>5</option>
                                 <option value="6" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_rechts'] == 6) ? 'selected' : '' ?>>6</option>
                                 <option value="0" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_rechts'] == 0) ? 'selected' : '' ?>>Noch nicht bewertet</option>
                                 <option value="9" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_rechts'] == 9) ? 'selected' : '' ?>>Bewertung ausgeschlossen</option>
                                 <option value="10" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_rechts'] == 10) ? 'selected' : '' ?>>Nicht vorhanden</option>
                                 <option value="11" <?= ($abschnittBewertung && $abschnittBewertung['seitenstreifen_rechts'] == 11) ? 'selected' : '' ?>>Wie Straße</option>
                             </select>
                         </td>
                         <td>
                             <select class="bewertung-dropdown" data-field="review" style="width: 50px;">
                                 <option value="0" <?= ($abschnittBewertung && $abschnittBewertung['review'] == 0) ? 'selected' : '' ?>>Nein</option>
                                 <option value="1" <?= ($abschnittBewertung && $abschnittBewertung['review'] == 1) ? 'selected' : '' ?>>Ja</option>
                             </select>
                         </td>
                         <td>-</td>
                     </tr>
                     <tr>
                         <td colspan="8">
                             <strong>Notizen:</strong><br>
                             <textarea id="abschnitt-notizen" style="width: 100%; height: 60px; margin-top: 5px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; font-size: 0.85rem;" placeholder="Notizen für diesen Straßenabschnitt..."><?= htmlspecialchars($abschnittBewertung['text'] ?? '') ?></textarea>
                         </td>
                     </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="log-section">
        <h2 class="collapsible-header" onclick="toggleLogSection()">
            <span class="toggle-icon">▶</span> Log
        </h2>
        <div id="log-content" class="collapsible-content" style="display: none;">
            <div class="log-table-container">
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Zeitstempel</th>
                            <th>Feld</th>
                            <th>Wert</th>
                            <th>Nutzer</th>
                        </tr>
                    </thead>
                    <tbody id="log-table-body">
                        <!-- Wird dynamisch gefüllt -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="bilder-table-container">
        <h2>Einzelbilder</h2>
        <table class="bilder-table">
            <thead>
                <tr>
                    <th>Bild-ID</th>
                    <th>Straße</th>
                    <th>Gehweg Links</th>
                    <th>Gehweg Rechts</th>
                    <th>Seitenstreifen Links</th>
                    <th>Seitenstreifen Rechts</th>
                    <th>Review</th>
                    <th>Schaden</th>
                </tr>
            </thead>
            <tbody id="bilder-table-body">
                <?php if (empty($bilder)): ?>
                    <tr>
                        <td colspan="8" class="no-data">Keine Bilder gefunden</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bilder as $bild): ?>
                        <tr>
                            <td>
                                <a href="#" onclick="showImageModal(<?= htmlspecialchars($bild['Id']) ?>, '<?= htmlspecialchars($bild['FileName']) ?>')" class="bild-id-link">
                                    <?= htmlspecialchars($bild['Id']) ?>
                                </a>
                            </td>
                            <td><?= $bild['strasse'] !== null ? htmlspecialchars($bild['strasse']) : '-' ?></td>
                            <td><?= $bild['gehweg_links'] !== null ? htmlspecialchars($bild['gehweg_links']) : '-' ?></td>
                            <td><?= $bild['gehweg_rechts'] !== null ? htmlspecialchars($bild['gehweg_rechts']) : '-' ?></td>
                            <td><?= $bild['seitenstreifen_links'] !== null ? htmlspecialchars($bild['seitenstreifen_links']) : '-' ?></td>
                            <td><?= $bild['seitenstreifen_rechts'] !== null ? htmlspecialchars($bild['seitenstreifen_rechts']) : '-' ?></td>
                            <td><?= $bild['review'] == 1 ? 'Ja' : 'Nein' ?></td>
                            <td><?= $bild['schaden'] == 1 ? 'Ja' : 'Nein' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bild-Modal -->
<div id="imageModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-header">
            <h3 id="modalTitle">Bild anzeigen</h3>
        </div>
        <div class="modal-body">
            <div class="modal-table-container">
                <table class="modal-table">
                    <thead>
                        <tr>
                            <th>Bild-ID</th>
                            <th>Straße</th>
                            <th>Gehweg Links</th>
                            <th>Gehweg Rechts</th>
                            <th>Seitenstreifen Links</th>
                            <th>Seitenstreifen Rechts</th>
                            <th>Review</th>
                            <th>Schaden</th>
                        </tr>
                    </thead>
                    <tbody id="modalTableBody">
                        <!-- Wird dynamisch gefüllt -->
                    </tbody>
                </table>
            </div>
            <img id="modalImage" src="" alt="Bild" style="max-width: 100%; max-height: 50vh; display: block; margin: 20px auto 0;">
        </div>
        <div class="modal-footer">
            <button id="goToBewertung" class="btn btn-primary">Zur Bewertung</button>
            <button class="btn btn-secondary" onclick="closeImageModal()">Schließen</button>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: none;
    width: 90%;
    max-width: 800px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.modal-body {
    padding: 20px;
    text-align: center;
}

.modal-table-container {
    margin-bottom: 20px;
    overflow-x: auto;
}

.modal-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
    margin: 0 auto;
}

.modal-table th {
    background: #f8f9fa;
    padding: 8px 6px;
    text-align: center;
    font-weight: 600;
    color: #495057;
    border: 1px solid #dee2e6;
    white-space: nowrap;
}

.modal-table td {
    padding: 8px 6px;
    border: 1px solid #dee2e6;
    text-align: center;
    font-weight: 600;
    color: #333;
}

.modal-table td:first-child {
    text-align: left;
    font-weight: 700;
    color: #007bff;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 0 0 8px 8px;
    text-align: right;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
}

.btn {
    padding: 8px 16px;
    margin-left: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
}

/* Log-Sektion Styles */
.log-section {
    margin-bottom: 30px;
}

.collapsible-header {
    cursor: pointer;
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px 8px 0 0;
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.collapsible-header:hover {
    background: #e9ecef;
}

.toggle-icon {
    font-size: 0.8rem;
    transition: transform 0.3s ease;
}

.collapsible-content {
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 8px 8px;
    background: white;
}

.log-table-container {
    padding: 20px;
    overflow-x: auto;
}

.log-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.log-table th {
    background: #f8f9fa;
    padding: 10px 8px;
    text-align: center;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}

.log-table td {
    padding: 8px;
    border-bottom: 1px solid #dee2e6;
    text-align: center;
    color: #333;
}

.log-table td:first-child {
    text-align: left;
}

.log-table tbody tr:hover {
    background: #f8f9fa;
}

/* Notizen Textarea Styles */
#abschnitt-notizen {
    resize: vertical;
    min-height: 60px;
}
</style>

<script>
let bilder = <?= json_encode($bilder) ?>;
let allAbschnitte = <?= json_encode($allAbschnitte) ?>;
let currentAbschnittId = <?= json_encode($abschnittId) ?>;
let currentIndex = 0;
let currentBildId = null;

// Modal-Funktionen
function showImageModal(bildId, fileName) {
    currentBildId = bildId;
    const modal = document.getElementById('imageModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalImage = document.getElementById('modalImage');
    const modalTableBody = document.getElementById('modalTableBody');
    
    modalTitle.textContent = `Bild ${bildId}: ${fileName}`;
    
    // Bewertungsdaten für das Bild finden
    const bildData = bilder.find(bild => bild.Id == bildId);
    
    // Tabelle mit Bewertungsdaten füllen
    if (bildData) {
        modalTableBody.innerHTML = `
            <tr>
                <td>${bildData.Id}</td>
                <td>${bildData.strasse !== null ? bildData.strasse : '-'}</td>
                <td>${bildData.gehweg_links !== null ? bildData.gehweg_links : '-'}</td>
                <td>${bildData.gehweg_rechts !== null ? bildData.gehweg_rechts : '-'}</td>
                <td>${bildData.seitenstreifen_links !== null ? bildData.seitenstreifen_links : '-'}</td>
                <td>${bildData.seitenstreifen_rechts !== null ? bildData.seitenstreifen_rechts : '-'}</td>
                <td>${bildData.review == 1 ? 'Ja' : 'Nein'}</td>
                <td>${bildData.schaden == 1 ? 'Ja' : 'Nein'}</td>
            </tr>
        `;
    } else {
        modalTableBody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; color: #999;">Keine Bewertungsdaten gefunden</td>
            </tr>
        `;
    }
    
    // Bild-URL genauso generieren wie in bilder.php
    const blobBaseUrl = '<?= $_ENV['BLOB_BASE_URL'] ?? '' ?>';
    const containerName = '<?= $_SESSION['AZURE_STORAGE_CONTAINER_NAME'] ?? '' ?>';
    const imageUrl = blobBaseUrl + containerName + '/' + encodeURIComponent(fileName);
    
    console.log('Loading image:', imageUrl); // Debug-Ausgabe
    
    modalImage.src = imageUrl;
    modalImage.onerror = function() {
        console.error('Failed to load image:', imageUrl);
        modalImage.alt = 'Bild konnte nicht geladen werden';
        modalImage.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkJpbGQgY29udXRlIG5pY2h0IGdlbGFkZW4gd2VyZGVuPC90ZXh0Pjwvc3ZnPg==';
    };
    
    modal.style.display = 'block';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
    currentBildId = null;
}

// Modal schließen bei Klick außerhalb
window.onclick = function(event) {
    const modal = document.getElementById('imageModal');
    if (event.target == modal) {
        closeImageModal();
    }
}

// Modal schließen bei ESC-Taste
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageModal();
    }
});

// Zur Bewertung Button
document.getElementById('goToBewertung').addEventListener('click', function() {
    if (currentBildId) {
        // Debug-Ausgabe
        console.log('Navigating to bewertung.php with:');
        console.log('- bildId:', currentBildId);
        console.log('- currentAbschnittId:', currentAbschnittId);
        
        // Abschnittsname für die Filter-Anzeige holen
        const currentAbschnitt = allAbschnitte.find(abschnitt => abschnitt.Id == currentAbschnittId);
        const abschnittName = currentAbschnitt ? encodeURIComponent(currentAbschnitt.abschnittname) : '';
        
        // Zur Bewertungsseite mit allen Bildern des Abschnitts navigieren
        // Der bildId-Parameter bestimmt, welches Bild initial angezeigt wird
        const url = `bewertung.php?bildId=${currentBildId}&filter=abschnitt&abschnittId=${currentAbschnittId}&abschnittName=${abschnittName}`;
        console.log('URL:', url);
        
        window.location.href = url;
    }
});

// Close Button
document.querySelector('.close').addEventListener('click', closeImageModal);

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

// Event-Listener für Dropdown-Änderungen
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.bewertung-dropdown');
    
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('change', function() {
            const field = this.getAttribute('data-field');
            const value = this.value;
            
            // Speichern in der Datenbank
            saveAbschnittBewertung(field, value);
        });
    });
    
    // Auto-Save für Notizen initialisieren
    setupNotizenAutoSave();
});

// Funktion zum Speichern der Abschnittsbewertung
function saveAbschnittBewertung(field, value) {
    // Verwende die aktuelle abschnittId aus der JavaScript-Variable
    const abschnittId = currentAbschnittId;
    
    fetch('save_abschnitt_bewertung.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `abschnittId=${abschnittId}&field=${field}&value=${value}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Bewertung erfolgreich gespeichert');
            // Log-Daten neu laden
            loadLogData();
        } else {
            console.error('Fehler beim Speichern:', data.error);
        }
    })
    .catch(error => {
        console.error('Fehler beim Speichern:', error);
    });
}

// Funktion zum Ein-/Ausklappen des Log-Bereichs
function toggleLogSection() {
    const content = document.getElementById('log-content');
    const icon = document.querySelector('.toggle-icon');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.textContent = '▼';
        loadLogData(); // Daten laden beim Öffnen
    } else {
        content.style.display = 'none';
        icon.textContent = '▶';
    }
}

// Funktion zum Laden der Log-Daten
function loadLogData() {
    // Verwende die aktuelle abschnittId aus der JavaScript-Variable
    const abschnittId = currentAbschnittId;
    
    fetch(`get_log_abschnitt_data.php?abschnittId=${abschnittId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLogData(data.logs);
            } else {
                console.error('Fehler beim Laden der Log-Daten:', data.error);
            }
        })
        .catch(error => {
            console.error('Fehler beim Laden der Log-Daten:', error);
        });
}

// Funktion zum Anzeigen der Log-Daten
function displayLogData(logs) {
    const tbody = document.getElementById('log-table-body');
    tbody.innerHTML = '';
    
    if (logs.length === 0) {
        const row = tbody.insertRow();
        const cell = row.insertCell(0);
        cell.colSpan = 4;
        cell.textContent = 'Keine Log-Einträge gefunden';
        cell.className = 'no-data';
        return;
    }
    
    logs.forEach(log => {
        const row = tbody.insertRow();
        row.insertCell(0).textContent = log.Zeitstempel || log.zeitstempel;
        row.insertCell(1).textContent = log.Feld || log.feld;
        row.insertCell(2).textContent = log.Wert || log.wert;
        row.insertCell(3).textContent = log.Nutzer || log.nutzer;
    });
}

// Auto-Save für Notizen
let notizenTimeout;

function setupNotizenAutoSave() {
    const textarea = document.getElementById('abschnitt-notizen');
    if (textarea) {
        textarea.addEventListener('input', function() {
            clearTimeout(notizenTimeout);
            notizenTimeout = setTimeout(() => {
                saveAbschnittNotizen(this.value);
            }, 1000); // 1 Sekunde Verzögerung
        });
    }
}

// Funktion zum Speichern der Notizen
function saveAbschnittNotizen(text) {
    // Verwende die aktuelle abschnittId aus der JavaScript-Variable
    const abschnittId = currentAbschnittId;
    
    fetch('save_abschnitt_bewertung.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `abschnittId=${abschnittId}&field=text&value=${encodeURIComponent(text)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Notizen erfolgreich gespeichert');
            loadLogData(); // Log-Daten neu laden
        } else {
            console.error('Fehler beim Speichern der Notizen:', data.error);
        }
    })
    .catch(error => {
        console.error('Fehler beim Speichern der Notizen:', error);
    });
}
</script>

</body>
</html>
