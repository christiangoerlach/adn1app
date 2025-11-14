<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Übersicht</title>
    <link rel="icon" href="https://adn-consulting.de/sites/default/files/favicon-96x96.png" type="image/png" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            font-family: 'Open Sans', sans-serif;
            background-color: #f4f4f4;
        }
        
        .top-bar {
            background-color: #003366;
            height: 8px;
            width: 100%;
        }
        
        .header {
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            border-bottom: 1px solid #ccc;
            position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header-left img {
            height: 50px;
            transition: opacity 0.2s;
        }
        
        .header-left a:hover img {
            opacity: 0.8;
        }
        
        .header-title {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 24px;
            font-weight: 600;
            color: #000;
        }
        
        .header-right {
            font-size: 14px;
            color: #333;
            text-align: right;
        }
        
        .header-right span {
            display: block;
        }
        
        .content {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 30px;
        }
        
        .welcome-section {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .welcome-section h1 {
            font-size: 32px;
            color: #003366;
            margin-bottom: 10px;
        }
        
        .welcome-section p {
            font-size: 18px;
            color: #666;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .menu-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            text-align: center;
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .menu-card-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .menu-card h2 {
            font-size: 24px;
            color: #003366;
            margin-bottom: 20px;
        }
        
        .menu-card-button {
            display: inline-block;
            background-color: #0078D4;
            color: white;
            padding: 12px 30px;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        
        .menu-card-button:hover {
            background-color: #005a9e;
        }
        
        .menu-card.bewertung {
            border-top: 4px solid #0078D4;
        }
        
        .menu-card.karte {
            border-top: 4px solid #28a745;
        }
        
        .menu-card.tools {
            border-top: 4px solid #ffc107;
        }
        
        .footer {
            margin-top: 60px;
            padding: 20px 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
        }
        
        .footer-version {
            margin: 5px 0;
        }
    </style>
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
        <span><?= htmlspecialchars($userInfo['username'] ?? 'Gast') ?></span>
        <?php if (!empty($userInfo['useremail'])): ?>
            <span><?= htmlspecialchars($userInfo['useremail']) ?></span>
        <?php endif; ?>
    </div>
</div>

<div class="content">
    <div class="welcome-section">
        <h1>Willkommen</h1>
        <p>Wählen Sie eine Funktion aus, um zu beginnen</p>
    </div>
    
    <div class="menu-grid">
        <div class="menu-card bewertung">
            <div class="menu-card-icon">⭐</div>
            <h2>Manuelle Bildbewertung</h2>
            <a href="/index.php?path=bewertungm" class="menu-card-button">Öffnen</a>
        </div>
        
        <div class="menu-card karte">
            <div class="menu-card-icon">🗺️</div>
            <h2>Netzknoten Modell</h2>
            <a href="/index.php?path=netzknoten" class="menu-card-button">Öffnen</a>
        </div>
        
        <div class="menu-card tools">
            <div class="menu-card-icon">📋</div>
            <h2>Abschnittsbewertung</h2>
            <a href="/index.php?path=abschnitt" class="menu-card-button">Öffnen</a>
        </div>
        
        <!-- Weitere Karten können hier hinzugefügt werden -->
    </div>
</div>

<footer class="footer">
    <div class="footer-version">
        Zuletzt geändert: <?php
            // Stelle sicher, dass die Zeitzone auf Europe/Berlin gesetzt ist
            date_default_timezone_set('Europe/Berlin');
            // Interpretiere APP_BUILD_DATE als UTC (Azure-Server laufen in UTC)
            // und konvertiere es nach Europe/Berlin
            $dateTime = new DateTime(APP_BUILD_DATE, new DateTimeZone('UTC'));
            $dateTime->setTimezone(new DateTimeZone('Europe/Berlin'));
            echo $dateTime->format('d.m.Y H:i');
        ?> Uhr
    </div>
</footer>

</body>
</html>

