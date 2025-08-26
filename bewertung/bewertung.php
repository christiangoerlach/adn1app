<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>ADN StraßenWeb - Bildergalerie</title>
    <link rel="icon" href="https://adn-consulting.de/sites/default/files/favicon-96x96.png" type="image/png" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { 
            text-align: center; 
            font-family: Arial, sans-serif; 
            margin: 20px;
        }
        
        .container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 30px;
            margin: 20px;
        }
        
        .image-section {
            flex: 1;
            min-width: 0;
        }
        
        .bewertung-section {
            flex: 0 0 350px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 10px;
        }
        
        #mainImage {
            max-width: 100%;
            max-height: 80vh;
            display: block;
            border: 2px solid #ccc;
            border-radius: 5px;
        }
        

        
        button { 
            font-size: 1.2rem; 
            padding: 10px 20px; 
            margin: 0 10px; 
            cursor: pointer;
        }
        
        button:disabled {
            opacity: 0.5;
            cursor: default;
        }
        
        .bewertung-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 12px 0;
        }
        
        .bewertung-btn {
            padding: 4px 6px;
            font-size: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 3px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .bewertung-btn:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }
        
        .bewertung-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .bewertung-row-1 {
            display: flex;
            gap: 4px;
            margin-bottom: 8px;
            justify-content: space-between;
        }
        
        .bewertung-row-1 .bewertung-btn {
            flex: 1;
        }
        
        .bewertung-row-2 {
            display: flex;
            gap: 6px;
            justify-content: space-between;
        }
        
        .bewertung-row-2 .bewertung-btn {
            flex: 1;
            padding: 6px 8px;
            font-size: 0.75rem;
            white-space: nowrap;
            text-align: center;
        }
        
        /* Spezielle Styles für die Straßen-Buttons in Zeile 2 */
        .strasse-row-2 {
            padding: 6px 8px !important;
            font-size: 0.75rem !important;
        }
        
        /* Neue Bewertungsabschnitte */
        .bewertung-abschnitte {
            margin: 12px 0;
        }
        
        .bewertung-abschnitt {
            margin-bottom: 10px;
            padding: 8px;
            background: white;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .bewertung-abschnitt h4 {
            margin: 0 0 6px 0;
            font-size: 0.8rem;
            color: #333;
            font-weight: 600;
        }
        
        .bewertung-dropdown {
            width: 100%;
            padding: 4px 6px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 0.75rem;
            background: white;
            cursor: pointer;
        }
        
        .bewertung-dropdown:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        
        /* Markierungsbuttons */
        .markierung-buttons {
            display: flex;
            gap: 8px;
            margin-top: 6px;
        }
        
        .markierung-btn {
            flex: 1;
            padding: 6px 8px;
            font-size: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 3px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .markierung-btn:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }
        
        .markierung-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        /* Notizen-Textarea */
        .notizen-textarea {
            width: 100%;
            min-height: 60px;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 0.75rem;
            font-family: Arial, sans-serif;
            resize: vertical;
            box-sizing: border-box;
        }
        
        .notizen-textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        
        .zeichen-zaehler {
            text-align: right;
            font-size: 0.7rem;
            color: #666;
            margin-top: 3px;
        }
        
        /* Status-Container mit fester Höhe */
        .status-container {
            min-height: 40px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .status {
            padding: 8px 12px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            font-size: 0.75rem;
            opacity: 0;
            transform: translateY(-5px);
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .status.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        #azureMap {
            border: 2px solid #ddd;
            border-radius: 5px;
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
        
        .menu-icon {
            cursor: pointer;
            font-size: 1.5rem;
            color: #007bff;
            padding: 8px;
            border-radius: 5px;
            transition: all 0.3s ease;
            user-select: none;
        }
        
        .menu-icon:hover {
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
        
        .log-table-container {
            overflow-x: auto;
            max-width: 100%;
        }
        
        .log-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            background: white;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .log-table th,
        .log-table td {
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .log-table th {
            background: #e9ecef;
            font-weight: 600;
            color: #495057;
        }
        
        .log-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        /* Ausklappbare Bereiche */
        .collapsible-section {
            margin-top: 12px;
        }
        
        .collapsible-header {
            cursor: pointer;
            padding: 10px 12px;
            background: #e9ecef;
            border-radius: 6px;
            margin: 0;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
            user-select: none;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .collapsible-header:hover {
            background: #dee2e6;
        }
        
        .toggle-icon {
            margin-right: 8px;
            font-size: 0.7rem;
            transition: transform 0.3s ease;
            color: #007bff;
        }
        
        .collapsible-content {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 0 0 6px 6px;
            border-top: 1px solid #dee2e6;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .collapsible-content.collapsed {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            border-top: none;
        }
        
        .collapsible-content.expanded {
            max-height: 1000px;
        }
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <div class="header-left">
        <div class="menu-icon" id="main-menu-btn" title="Hauptmenü">☰</div>
        <h1>
            <span id="project-name">Lade Projekt...</span>: <span id="current-image-name">Lade Bild...</span>
        </h1>
    </div>
    <div class="header-center">
        <div class="nav-buttons">
            <button id="prevBtn" disabled>← Zurück</button>
            <span id="counter">0 / 0</span>
            <button id="nextBtn" disabled>Vor →</button>
        </div>
        <div id="filter-info" style="margin-left: 20px; font-size: 1.2rem; color: #666;"></div>
    </div>
    <div class="header-right">
        <span id="current-user">Lade Benutzer...</span>
    </div>
</div>

<div class="container">
    <div class="image-section">
        <img id="mainImage" src="" alt="Bild" />
    </div>
    
    <div class="bewertung-section">
        <!-- Status-Bereich mit fester Höhe -->
        <div id="bewertung-status" class="status-container"></div>
        
        <!-- Straße ausklappbarer Abschnitt -->
        <div class="collapsible-section strasse-section">
            <h3 class="collapsible-header" data-target="strasse-content">
                <span class="toggle-icon">▶</span>
                Straße
            </h3>
            <div id="strasse-content" class="collapsible-content collapsed">
                <!-- Straßenbewertung -->
                <div class="bewertung-abschnitt">
                    <h4>Straßenzustandsklasse</h4>
                    <div class="bewertung-buttons">
                        <div class="bewertung-row-1">
                            <button class="bewertung-btn" data-value="1">1</button>
                            <button class="bewertung-btn" data-value="2">2</button>
                            <button class="bewertung-btn" data-value="3">3</button>
                            <button class="bewertung-btn" data-value="4">4</button>
                            <button class="bewertung-btn" data-value="5">5</button>
                            <button class="bewertung-btn" data-value="6">6</button>
                        </div>
                        <div class="bewertung-row-2">
                            <button class="bewertung-btn strasse-row-2" data-value="0">Noch nicht bewertet</button>
                            <button class="bewertung-btn strasse-row-2" data-value="9">Bewertung ausgeschlossen</button>
                        </div>
                    </div>
                </div>
                
                <!-- Review und Schaden Buttons -->
                <div class="bewertung-abschnitt">
                    <h4>Markierungen</h4>
                    <div class="markierung-buttons">
                        <button class="markierung-btn" id="review-btn" data-feld="review" data-value="0">Review</button>
                        <button class="markierung-btn" id="schaden-btn" data-feld="schaden" data-value="0">Schaden</button>
                    </div>
                </div>
                
                <!-- Freitext-Notizen -->
                <div class="bewertung-abschnitt">
                    <h4>Notizen</h4>
                    <textarea id="notizen-text" class="notizen-textarea" placeholder="Freitext-Notizen hier eingeben..." maxlength="1000"></textarea>
                    <div class="zeichen-zaehler">
                        <span id="zeichen-anzahl">0</span> / 1000 Zeichen
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gehweg/Seitenstreifen ausklappbarer Abschnitt -->
        <div class="collapsible-section gehweg-section">
            <h3 class="collapsible-header" data-target="gehweg-content">
                <span class="toggle-icon">▶</span>
                Gehweg/Seitenstreifen
            </h3>
            <div id="gehweg-content" class="collapsible-content collapsed">
                <div class="bewertung-abschnitte">
                                    <div class="bewertung-abschnitt">
                    <h4>Gehweg Links</h4>
                    <select class="bewertung-dropdown" data-feld="gehweg_links" data-bild-id="">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="0">Noch nicht bewertet</option>
                        <option value="9">Bewertung ausgeschlossen</option>
                        <option value="10">Nicht vorhanden</option>
                        <option value="11">Wie Straße</option>
                    </select>
                </div>
                    
                    <div class="bewertung-abschnitt">
                        <h4>Gehweg Rechts</h4>
                        <select class="bewertung-dropdown" data-feld="gehweg_rechts" data-bild-id="">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="0">Noch nicht bewertet</option>
                            <option value="9">Bewertung ausgeschlossen</option>
                            <option value="10">Nicht vorhanden</option>
                            <option value="11">Wie Straße</option>
                        </select>
                    </div>
                    
                    <div class="bewertung-abschnitt">
                        <h4>Seitenstreifen Links</h4>
                        <select class="bewertung-dropdown" data-feld="seitenstreifen_links" data-bild-id="">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="0">Noch nicht bewertet</option>
                            <option value="9">Bewertung ausgeschlossen</option>
                            <option value="10">Nicht vorhanden</option>
                            <option value="11">Wie Straße</option>
                        </select>
                    </div>
                    
                    <div class="bewertung-abschnitt">
                        <h4>Seitenstreifen Rechts</h4>
                        <select class="bewertung-dropdown" data-feld="seitenstreifen_rechts" data-bild-id="">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="0">Noch nicht bewertet</option>
                            <option value="9">Bewertung ausgeschlossen</option>
                            <option value="10">Nicht vorhanden</option>
                            <option value="11">Wie Straße</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="collapsible-section map-section">
            <h3 class="collapsible-header" data-target="map-content">
                <span class="toggle-icon">▶</span>
                Kartenansicht
            </h3>
            <div id="map-content" class="collapsible-content collapsed">
                <div id="azureMap" style="width: 100%; height: 300px;"></div>
            </div>
        </div>
        
        <div class="collapsible-section log-section">
            <h3 class="collapsible-header" data-target="log-content">
                <span class="toggle-icon">▶</span>
                Log
            </h3>
            <div id="log-content" class="collapsible-content collapsed">
                <div class="log-table-container">
                    <table id="log-table" class="log-table">
                        <thead>
                            <tr>
                                <th>Datum/Zeit</th>
                                <th>Nutzer</th>
                                <th>Feld</th>
                                <th>Wert</th>
                            </tr>
                        </thead>
                        <tbody id="log-table-body">
                            <tr>
                                <td colspan="4">Lade Log-Daten...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="collapsible-section abschnitt-section">
            <h3 class="collapsible-header" data-target="abschnitt-content">
                <span class="toggle-icon">▶</span>
                Straßenabschnitt
            </h3>
            <div id="abschnitt-content" class="collapsible-content collapsed">
                <div id="abschnitt-info" style="padding: 12px; background: white; border-radius: 4px; border: 1px solid #ddd; font-size: 0.8rem; color: #333;">
                    Lade Abschnittsinformationen...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let images = [];
let currentIndex = 0;
let currentBildId = null;
let saveNotizenTimeout = null; // Für Debouncing der Notizen-Speicherung

// Erweiterte Bildvorlade-Strategie
function preloadImages(currentIndex) {
    // Vorladen der nächsten 3 Bilder für flüssige Navigation
    for (let i = 1; i <= 3; i++) {
        const preloadIndex = currentIndex + i;
        if (preloadIndex < images.length) {
            preloadImage(images[preloadIndex]);
        }
    }
    
    // Auch die vorherigen 2 Bilder vorladen für Zurück-Navigation
    for (let i = 1; i <= 2; i++) {
        const preloadIndex = currentIndex - i;
        if (preloadIndex >= 0) {
            preloadImage(images[preloadIndex]);
        }
    }
}

function preloadImage(imageData) {
    const img = new Image();
    img.src = imageData.url;
}

function updateImage() {
    if(images.length === 0) {
        document.getElementById('mainImage').src = '';
        document.getElementById('mainImage').alt = 'Keine Bilder gefunden';
        document.getElementById('counter').textContent = '0 / 0';
        document.getElementById('prevBtn').disabled = true;
        document.getElementById('nextBtn').disabled = true;
        return;
    }

    const currentImage = images[currentIndex];
    const img = document.getElementById('mainImage');
    img.src = currentImage.url;
    img.alt = `Bild ${currentIndex + 1}`;
    document.getElementById('counter').textContent = `${currentIndex + 1} / ${images.length}`;
    
    // Aktuelle Bild-ID setzen
    currentBildId = currentImage.id;
    
    // Bildname im Header aktualisieren
    const fileName = currentImage.url.split('/').pop().split('?')[0];
    document.getElementById('current-image-name').textContent = decodeURIComponent(fileName);
    
    // Bewertung für das aktuelle Bild laden
    loadBewertung(currentBildId);

    // Enable/disable buttons accordingly
    document.getElementById('prevBtn').disabled = currentIndex === 0;
    document.getElementById('nextBtn').disabled = currentIndex === images.length - 1;

    // Erweiterte Bildvorlade-Strategie für bessere Performance
    preloadImages(currentIndex);
}

// Bewertung für ein Bild laden
function loadBewertung(bildId) {
    fetch(`get_bewertung.php?bildId=${bildId}`)
        .then(response => response.json())
        .then(data => {
            if (data.strasse !== undefined) {
                updateBewertungButtons(data.strasse);
            }
            // Neue Felder laden
            updateDropdownValues(data);
        })
        .catch(error => {
            console.error('Fehler beim Laden der Bewertung:', error);
        });
    
    // Log-Daten für das aktuelle Bild laden
    loadLogData(bildId);
    
    // Abschnittsinformationen laden
    loadAbschnittInfo(bildId);
}

// Bewertungsbuttons aktualisieren
function updateBewertungButtons(strasse) {
    // Alle Buttons zurücksetzen
    document.querySelectorAll('.bewertung-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Aktiven Button markieren
    const activeBtn = document.querySelector(`[data-value="${strasse}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
}

// Dropdown-Werte aktualisieren
function updateDropdownValues(data) {
    const felder = ['gehweg_links', 'gehweg_rechts', 'seitenstreifen_links', 'seitenstreifen_rechts'];
    
    felder.forEach(feld => {
        const dropdown = document.querySelector(`[data-feld="${feld}"]`);
        if (dropdown && data[feld] !== undefined && data[feld] !== null) {
            dropdown.value = data[feld];
        } else if (dropdown) {
            // Kein Wert setzen - Dropdown bleibt leer
            dropdown.selectedIndex = -1;
        }
    });
    
    // Markierungsbuttons aktualisieren
    updateMarkierungButtons(data);
    
    // Notizen aktualisieren
    updateNotizen(data);
}

// Bewertung speichern
function saveBewertung(strasse) {
    if (!currentBildId) return;
    
    // Sofortige UI-Updates für bessere Performance
    updateBewertungButtons(strasse);
    
    // Optimistisches Update - Log-Tabelle sofort neu laden
    invalidateLogCache(currentBildId);
    loadLogData(currentBildId);
    
    fetch('save_bewertung.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            bildId: currentBildId,
            strasse: strasse
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            showStatus('Bewertung erfolgreich gespeichert!', 'success');
        } else {
            const msg = (data && data.error) ? data.error : 'Fehler beim Speichern der Bewertung';
            console.error('Speicher-Response:', data);
            showStatus(msg, 'error');
            // Bei Fehler UI zurücksetzen
            loadBewertung(currentBildId);
        }
    })
    .catch(error => {
        console.error('Fehler beim Speichern der Bewertung:', error);
        showStatus('Fehler beim Speichern der Bewertung', 'error');
        // Bei Fehler UI zurücksetzen
        loadBewertung(currentBildId);
    });
}

// Status anzeigen
function showStatus(message, type) {
    const statusContainer = document.getElementById('bewertung-status');
    
    // Neues Status-Element erstellen
    const statusDiv = document.createElement('div');
    statusDiv.textContent = message;
    statusDiv.className = `status ${type}`;
    
    // Container leeren und neues Element hinzufügen
    statusContainer.innerHTML = '';
    statusContainer.appendChild(statusDiv);
    
    // Animation einblenden
    setTimeout(() => {
        statusDiv.classList.add('show');
    }, 10);
    
    // Status nach 3 Sekunden ausblenden
    setTimeout(() => {
        statusDiv.classList.remove('show');
        setTimeout(() => {
            if (statusContainer.contains(statusDiv)) {
                statusContainer.removeChild(statusDiv);
            }
        }, 300); // Warten auf Fade-out Animation
    }, 3000);
}

// Dropdown-Bewertung speichern
function saveDropdownBewertung(feld, wert) {
    if (!currentBildId) return;
    
    // Optimistisches Update - Log-Tabelle sofort neu laden
    invalidateLogCache(currentBildId);
    loadLogData(currentBildId);
    
    fetch('save_dropdown_bewertung.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            bildId: currentBildId,
            feld: feld,
            wert: wert
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            showStatus(`${feld.replace('_', ' ')} Bewertung erfolgreich gespeichert!`, 'success');
        } else {
            const msg = (data && data.error) ? data.error : 'Fehler beim Speichern der Bewertung';
            console.error('Speicher-Response:', data);
            showStatus(msg, 'error');
            // Bei Fehler UI zurücksetzen
            loadBewertung(currentBildId);
        }
    })
    .catch(error => {
        console.error('Fehler beim Speichern der Dropdown-Bewertung:', error);
        showStatus('Fehler beim Speichern der Bewertung', 'error');
        // Bei Fehler UI zurücksetzen
        loadBewertung(currentBildId);
    });
}

// Markierungsbuttons aktualisieren
function updateMarkierungButtons(data) {
    const reviewBtn = document.getElementById('review-btn');
    const schadenBtn = document.getElementById('schaden-btn');
    
    if (reviewBtn && data.review !== undefined) {
        // Setze den aktuellen Wert aus der Datenbank
        const reviewWert = parseInt(data.review) || 0;
        reviewBtn.setAttribute('data-value', reviewWert);
        reviewBtn.classList.toggle('active', reviewWert === 1);
    }
    
    if (schadenBtn && data.schaden !== undefined) {
        // Setze den aktuellen Wert aus der Datenbank
        const schadenWert = parseInt(data.schaden) || 0;
        schadenBtn.setAttribute('data-value', schadenWert);
        schadenBtn.classList.toggle('active', schadenWert === 1);
    }
}

// Notizen aktualisieren
function updateNotizen(data) {
    const notizenText = document.getElementById('notizen-text');
    if (notizenText && data.text !== undefined) {
        notizenText.value = data.text || '';
        updateZeichenZaehler();
    }
}

// Markierung speichern
function saveMarkierung(feld, wert) {
    if (!currentBildId) return;
    
    // Optimistisches Update - Log-Tabelle sofort neu laden
    invalidateLogCache(currentBildId);
    loadLogData(currentBildId);
    
    fetch('save_markierung.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            bildId: currentBildId,
            feld: feld,
            wert: wert
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            showStatus(`${feld} erfolgreich gespeichert!`, 'success');
        } else {
            const msg = (data && data.error) ? data.error : 'Fehler beim Speichern der Markierung';
            console.error('Speicher-Response:', data);
            showStatus(msg, 'error');
            // Bei Fehler UI zurücksetzen
            loadBewertung(currentBildId);
        }
    })
    .catch(error => {
        console.error('Fehler beim Speichern der Markierung:', error);
        showStatus('Fehler beim Speichern der Markierung', 'error');
        // Bei Fehler UI zurücksetzen
        loadBewertung(currentBildId);
    });
}

// Notizen speichern
function saveNotizen(notizen) {
    if (!currentBildId) return;
    
    // Optimistisches Update - Log-Tabelle sofort neu laden
    invalidateLogCache(currentBildId);
    loadLogData(currentBildId);
    
    fetch('save_notizen.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            bildId: currentBildId,
            text: notizen
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            showStatus('Notizen erfolgreich gespeichert!', 'success');
        } else {
            const msg = (data && data.error) ? data.error : 'Fehler beim Speichern der Notizen';
            console.error('Speicher-Response:', data);
            showStatus(msg, 'error');
            // Bei Fehler UI zurücksetzen
            loadBewertung(currentBildId);
        }
    })
    .catch(error => {
        console.error('Fehler beim Speichern der Notizen:', error);
        showStatus('Fehler beim Speichern der Notizen', 'error');
        // Bei Fehler UI zurücksetzen
            loadBewertung(currentBildId);
    });
}

// Zeichenzähler aktualisieren
function updateZeichenZaehler() {
    const notizenText = document.getElementById('notizen-text');
    const zeichenAnzahl = document.getElementById('zeichen-anzahl');
    
    if (notizenText && zeichenAnzahl) {
        const aktuelleZeichen = notizenText.value.length;
        zeichenAnzahl.textContent = aktuelleZeichen;
        
        // Farbe ändern wenn 80% erreicht sind
        if (aktuelleZeichen >= 800) {
            zeichenAnzahl.style.color = '#dc3545';
        } else if (aktuelleZeichen >= 600) {
            zeichenAnzahl.style.color = '#ffc107';
        } else {
            zeichenAnzahl.style.color = '#666';
        }
    }
}

function loadImages() {
    // URL-Parameter für Filter holen
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter') || 'all';
    const wert = urlParams.get('wert') || '';
    const feld = urlParams.get('feld') || '';
    const abschnittId = urlParams.get('abschnittId') || '';
    
    // Filter-Text anzeigen
    updateFilterInfo(filter, wert);
    
    // Filter-Parameter an bilder.php weiterleiten
    let fetchUrl = 'bilder.php?filter=' + encodeURIComponent(filter);
    if (wert !== '') {
        fetchUrl += '&wert=' + encodeURIComponent(wert);
    }
    if (feld !== '') {
        fetchUrl += '&feld=' + encodeURIComponent(feld);
    }
    if (abschnittId !== '') {
        fetchUrl += '&abschnittId=' + encodeURIComponent(abschnittId);
    }
    
    fetch(fetchUrl)
        .then(response => response.json())
        .then(data => {
            // Expect data.images to be an array of URLs
            if (Array.isArray(data.images)) {
                images = data.images;
            } else if (Array.isArray(data)) {
                images = data;
            } else {
                images = [];
            }

            currentIndex = 0;
            updateImage();
        })
        .catch(error => {
            console.error('Fehler beim Laden der Bilder:', error);
            images = [];
            updateImage();
        });
}

// Filter-Information anzeigen
function updateFilterInfo(filter, wert) {
    const filterInfo = document.getElementById('filter-info');
    let filterText = 'Filter: ';
    
    if (filter === 'all') {
        filterText += 'Alle Bilder';
    } else if (filter === 'zustand') {
        filterText += `Zustand ${wert}`;
    } else if (filter === 'nicht_bewertet') {
        filterText += 'Nicht bewertet';
    } else if (filter === 'zugeordnet') {
        filterText += 'Zugeordnet';
    } else if (filter === 'nicht_zugeordnet') {
        filterText += 'Nicht zugeordnet';
    } else if (filter === 'straßenabschnitte') {
        let wertText = '';
        if (wert >= 1 && wert <= 6) {
            wertText = `Zustand ${wert}`;
        } else if (wert === 0) {
            wertText = 'Nicht bewertet';
        } else if (wert === 9) {
            wertText = 'Ausgeschlossen';
        } else if (wert === 10) {
            wertText = 'Nicht vorhanden';
        } else if (wert === 11) {
            wertText = 'Wie Straße';
        }
        
        filterText += `Straßenabschnitte - ${wertText}`;
    } else if (filter === 'abschnitt') {
        filterText += 'Straßenabschnitt';
    } else {
        filterText += 'Unbekannt';
    }
    
    filterInfo.textContent = filterText;
}

document.getElementById('prevBtn').addEventListener('click', () => {
    if (currentIndex > 0) {
        currentIndex--;
        updateImage();
    }
});

document.getElementById('nextBtn').addEventListener('click', () => {
    if (currentIndex < images.length - 1) {
        currentIndex++;
        updateImage();
    }
});

// Azure Maps Funktionalität
let map = null;
let currentStyle = 'road';
let azureMapsKey = '';

// Azure Maps initialisieren
async function initAzureMap() {
    try {
        // Azure Maps Key laden
        const response = await fetch('get_map_key.php');
        const data = await response.json();
        
        if (data.error) {
            console.error('Fehler beim Laden des Azure Maps Keys:', data.error);
            return;
        }
        
        azureMapsKey = data.key;
        
        // Azure Maps laden (neueste Version)
        const script = document.createElement('script');
        script.src = `https://atlas.microsoft.com/sdk/javascript/mapcontrol/3/atlas.min.js`;
        script.onload = () => {
            // Azure Maps CSS laden
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://atlas.microsoft.com/sdk/javascript/mapcontrol/3/atlas.min.css';
            document.head.appendChild(link);
            
            // Karte initialisieren
            map = new atlas.Map('azureMap', {
                center: [8.700000, 50.516000], // [longitude, latitude]
                zoom: 14, // Detaillierter Zoom für Straßenansicht
                style: 'road',
                authOptions: {
                    authType: 'subscriptionKey',
                    subscriptionKey: azureMapsKey
                }
            });
            
            // Style Picker Control hinzufügen - nur Straße und Satellit mit Straßennamen
            map.controls.add(new atlas.control.StyleControl({
                mapStyles: ['road', 'satellite_road_labels']
            }), {
                position: 'top-right'
            });
            
            // Karte ist geladen
            map.events.add('ready', () => {
                console.log('Azure Map geladen');
                
                // Markierung in die Mitte des Kartenausschnitts setzen
                const marker = new atlas.HtmlMarker({
                    htmlContent: '<div style="background-color: #007bff; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                    position: [8.700000, 50.516000]
                });
                
                map.markers.add(marker);
            });
        };
        document.head.appendChild(script);
        
    } catch (error) {
        console.error('Fehler beim Initialisieren der Azure Map:', error);
    }
}

// Event-Listener für Kartensteuerung
document.addEventListener('DOMContentLoaded', function() {
    // Hauptmenü-Button
    document.getElementById('main-menu-btn').addEventListener('click', function() {
        window.location.href = '../index.php';
    });
    
    // Bewertungsbuttons
    document.querySelectorAll('.bewertung-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const strasse = parseInt(this.getAttribute('data-value'));
            saveBewertung(strasse);
        });
    });
    
    // Dropdown-Event-Listener
    document.querySelectorAll('.bewertung-dropdown').forEach(dropdown => {
        dropdown.addEventListener('change', function() {
            const feld = this.getAttribute('data-feld');
            const wert = this.value;
            if (wert !== '') {
                saveDropdownBewertung(feld, parseInt(wert));
            }
        });
    });
    
    // Markierungsbutton-Event-Listener
    document.querySelectorAll('.markierung-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const feld = this.getAttribute('data-feld');
            const aktuellerWert = parseInt(this.getAttribute('data-value'));
            const neuerWert = aktuellerWert === 1 ? 0 : 1;
            
            // Button-Status sofort aktualisieren
            this.setAttribute('data-value', neuerWert);
            this.classList.toggle('active', neuerWert === 1);
            
            // In Datenbank speichern
            saveMarkierung(feld, neuerWert);
        });
    });
    
    // Notizen-Event-Listener
    const notizenText = document.getElementById('notizen-text');
    if (notizenText) {
        // Zeichenzähler beim Tippen aktualisieren
        notizenText.addEventListener('input', updateZeichenZaehler);
        
        // Notizen mit Debouncing speichern (500ms Verzögerung)
        notizenText.addEventListener('input', function() {
            clearTimeout(saveNotizenTimeout);
            saveNotizenTimeout = setTimeout(() => {
                const notizen = this.value.trim();
                saveNotizen(notizen);
            }, 500);
        });
        
        // Notizen sofort speichern wenn das Feld verlassen wird
        notizenText.addEventListener('blur', function() {
            clearTimeout(saveNotizenTimeout);
            const notizen = this.value.trim();
            saveNotizen(notizen);
        });
        
        // Initial Zeichenzähler aktualisieren
        updateZeichenZaehler();
    }
    
    // Ausklappbare Bereiche
    document.querySelectorAll('.collapsible-header').forEach(header => {
        header.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const content = document.getElementById(targetId);
            const toggleIcon = this.querySelector('.toggle-icon');
            
            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                content.classList.add('expanded');
                toggleIcon.textContent = '▼';
                toggleIcon.style.transform = 'rotate(0deg)';
            } else {
                content.classList.remove('expanded');
                content.classList.add('collapsed');
                toggleIcon.textContent = '▶';
                toggleIcon.style.transform = 'rotate(0deg)';
            }
        });
    });
    
    // Kartensteuerung - Zoom wird direkt über die Karte gesteuert
    
    // Azure Map initialisieren
    initAzureMap();
    
    // Projektnamen laden
    loadProjectName();
    
    // Benutzerinformationen laden
    loadUserInfo();
});

// Log-Daten für ein Bild laden
function loadLogData(bildId) {
    // Cache für Log-Daten um doppelte Anfragen zu vermeiden
    if (window.logCache && window.logCache[bildId]) {
        updateLogTable(window.logCache[bildId]);
        return;
    }
    
    fetch(`get_log_data.php?bildId=${bildId}`)
        .then(response => response.json())
        .then(data => {
            // Cache aktualisieren
            if (!window.logCache) window.logCache = {};
            window.logCache[bildId] = data;
            updateLogTable(data);
        })
        .catch(error => {
            console.error('Fehler beim Laden der Log-Daten:', error);
            updateLogTable([]);
        });
}

// Log-Tabelle aktualisieren
function updateLogTable(logData) {
    const tbody = document.getElementById('log-table-body');
    
    if (!logData || logData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4">Keine Log-Einträge vorhanden</td></tr>';
        return;
    }
    
    tbody.innerHTML = logData.map(log => `
        <tr>
            <td>${formatDateTime(log.Zeitstempel)}</td>
            <td>${log.Nutzer}</td>
            <td>${log.Feld}</td>
            <td>${log.Wert}</td>
        </tr>
    `).join('');
}

// Cache für Log-Daten invalidieren (wird aufgerufen wenn neue Einträge hinzugefügt werden)
function invalidateLogCache(bildId) {
    if (window.logCache && window.logCache[bildId]) {
        delete window.logCache[bildId];
    }
}

// Datum/Zeit formatieren
function formatDateTime(dateTimeString) {
    if (!dateTimeString) return '-';
    
    try {
        const date = new Date(dateTimeString);
        
        // Verwende die deutsche Zeitzone direkt
        return date.toLocaleString('de-DE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            timeZone: 'Europe/Berlin'
        });
    } catch (error) {
        return dateTimeString;
    }
}

// Load images on page load
loadImages();

// Projektnamen laden
async function loadProjectName() {
    try {
        const response = await fetch('get_project_name.php');
        const data = await response.json();
        
        if (data.projektname) {
            document.getElementById('project-name').textContent = data.projektname;
        } else {
            document.getElementById('project-name').textContent = 'Unbekanntes Projekt';
        }
    } catch (error) {
        console.error('Fehler beim Laden des Projektnamens:', error);
        document.getElementById('project-name').textContent = 'Unbekanntes Projekt';
    }
}

// Benutzerinformationen laden
async function loadUserInfo() {
    try {
        const response = await fetch('get_user_info.php');
        const data = await response.json();
        
        if (data.username) {
            document.getElementById('current-user').textContent = data.username;
        } else {
            document.getElementById('current-user').textContent = 'Gast';
        }
    } catch (error) {
        console.error('Fehler beim Laden der Benutzerinformationen:', error);
        document.getElementById('current-user').textContent = 'Gast';
    }
}

// Abschnittsinformationen laden
async function loadAbschnittInfo(bildId) {
    try {
        const response = await fetch(`get_abschnitt_info.php?bildId=${bildId}`);
        const data = await response.json();
        
        const abschnittInfo = document.getElementById('abschnitt-info');
        if (abschnittInfo) {
            if (data.abschnittname) {
                abschnittInfo.textContent = data.abschnittname;
            } else {
                abschnittInfo.textContent = 'Nicht zugeordnet';
            }
        }
    } catch (error) {
        console.error('Fehler beim Laden der Abschnittsinformationen:', error);
        const abschnittInfo = document.getElementById('abschnitt-info');
        if (abschnittInfo) {
            abschnittInfo.textContent = 'Fehler beim Laden';
        }
    }
}
</script>

</body>
</html>
