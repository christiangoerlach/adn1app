<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>AJAX Bildergalerie aus Azure Blob</title>
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
    </style>
</head>
<body>

<h1>Bildergalerie 1441</h1>

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

// Event-Listener für Bewertungsbuttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.bewertung-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const strasse = parseInt(this.getAttribute('data-value'));
            saveBewertung(strasse);
        });
    });
});

// Load images on page load
loadImages();
</script>

</body>
</html>
