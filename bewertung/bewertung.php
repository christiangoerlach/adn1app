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
            max-width: 60%;
        }
        
        .bewertung-section {
            flex: 1;
            max-width: 40%;
            padding: 20px;
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
        
        .nav-buttons { 
            margin: 20px; 
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
            gap: 10px;
            margin: 20px 0;
        }
        
        .bewertung-btn {
            padding: 15px 20px;
            font-size: 1.1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
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
        
        .bewertung-btn.nicht-bewertet {
            background: #6c757d;
            color: white;
            border-color: #6c757d;
        }
        
        .bewertung-btn.nicht-bewertet.active {
            background: #495057;
            border-color: #495057;
        }
        
        .status {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
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
        
        .map-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
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
        
        .header-left h1 {
            margin: 0;
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .header-right {
            font-weight: 600;
            color: #007bff;
            font-size: 0.9rem;
        }
        
        .log-section {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
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
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <div class="header-left">
        <h1 id="current-image-name">Lade Bild...</h1>
    </div>
    <div class="header-right">
        <span id="current-user">Lade Benutzer...</span>
    </div>
</div>

<div class="container">
    <div class="image-section">
        <img id="mainImage" src="" alt="Bild" />
        <div class="nav-buttons">
            <button id="prevBtn" disabled>← Zurück</button>
            <span id="counter">0 / 0</span>
            <button id="nextBtn" disabled>Vor →</button>
        </div>
    </div>
    
    <div class="bewertung-section">
        <h3>Straßenbewertung</h3>
        <p>Bitte bewerten Sie die Straßenqualität:</p>
        
        <div class="bewertung-buttons">
            <button class="bewertung-btn" data-value="1">1</button>
            <button class="bewertung-btn" data-value="2">2</button>
            <button class="bewertung-btn" data-value="3">3</button>
            <button class="bewertung-btn" data-value="4">4</button>
            <button class="bewertung-btn" data-value="5">5</button>
            <button class="bewertung-btn" data-value="6">6</button>
            <button class="bewertung-btn nicht-bewertet" data-value="0">Noch nicht bewertet</button>
        </div>
        
        <div id="bewertung-status"></div>
        
        <div class="map-section">
            <h3>Kartenansicht</h3>
            <div id="azureMap" style="width: 100%; height: 300px;"></div>
        </div>
        
        <div class="log-section">
            <h3>Log</h3>
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
</div>

<script>
let images = [];
let currentIndex = 0;
let currentBildId = null;

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

    // Preload next 2 images for smoother experience
    for (let i = 1; i <= 2; i++) {
        const preloadIndex = currentIndex + i;
        if (preloadIndex < images.length) {
            preloadImage(images[preloadIndex]);
        }
    }
}

// Bewertung für ein Bild laden
function loadBewertung(bildId) {
    fetch(`get_bewertung.php?bildId=${bildId}`)
        .then(response => response.json())
        .then(data => {
            if (data.strasse !== undefined) {
                updateBewertungButtons(data.strasse);
            }
        })
        .catch(error => {
            console.error('Fehler beim Laden der Bewertung:', error);
        });
    
    // Log-Daten für das aktuelle Bild laden
    loadLogData(bildId);
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

// Bewertung speichern
function saveBewertung(strasse) {
    if (!currentBildId) return;
    
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
            updateBewertungButtons(strasse);
            showStatus('Bewertung erfolgreich gespeichert!', 'success');
            
            // Log-Tabelle neu laden nach Bewertungsänderung
            if (currentBildId) {
                loadLogData(currentBildId);
            }
        } else {
            const msg = (data && data.error) ? data.error : 'Fehler beim Speichern der Bewertung';
            console.error('Speicher-Response:', data);
            showStatus(msg, 'error');
        }
    })
    .catch(error => {
        console.error('Fehler beim Speichern der Bewertung:', error);
        showStatus(error && error.message ? error.message : 'Fehler beim Speichern der Bewertung', 'error');
    });
}

// Status anzeigen
function showStatus(message, type) {
    const statusDiv = document.getElementById('bewertung-status');
    statusDiv.textContent = message;
    statusDiv.className = `status ${type}`;
    
    // Status nach 3 Sekunden ausblenden
    setTimeout(() => {
        statusDiv.textContent = '';
        statusDiv.className = '';
    }, 3000);
}

function loadImages() {
    fetch('bilder.php')
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
    // Bewertungsbuttons
    document.querySelectorAll('.bewertung-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const strasse = parseInt(this.getAttribute('data-value'));
            saveBewertung(strasse);
        });
    });
    
    // Kartensteuerung - Zoom wird direkt über die Karte gesteuert
    
    // Azure Map initialisieren
    initAzureMap();
    
    // Benutzerinformationen laden
    loadUserInfo();
});

// Log-Daten für ein Bild laden
function loadLogData(bildId) {
    fetch(`get_log_data.php?bildId=${bildId}`)
        .then(response => response.json())
        .then(data => {
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
            <td>${formatDateTime(log.CreatedAt)}</td>
            <td>${log.Nutzer}</td>
            <td>${log.Feld}</td>
            <td>${log.Wert}</td>
        </tr>
    `).join('');
}

// Datum/Zeit formatieren
function formatDateTime(dateTimeString) {
    if (!dateTimeString) return '-';
    
    try {
        const date = new Date(dateTimeString);
        return date.toLocaleString('de-DE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    } catch (error) {
        return dateTimeString;
    }
}

// Load images on page load
loadImages();

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
</script>

</body>
</html>
