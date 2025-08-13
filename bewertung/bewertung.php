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
        #mainImage {
            max-width: 80vw;
            max-height: 80vh;
            margin: 20px auto;
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
    </style>
</head>
<body>

<h1>Bildergalerie 1438</h1>

<img id="mainImage" src="" alt="Bild" />
<div class="nav-buttons">
    <button id="prevBtn" disabled>← Zurück</button>
    <span id="counter">0 / 0</span>
    <button id="nextBtn" disabled>Vor →</button>
</div>

<script>
let images = [];
let currentIndex = 0;

function preloadImage(url) {
    const img = new Image();
    img.src = url;
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

    const img = document.getElementById('mainImage');
    img.src = images[currentIndex];
    img.alt = `Bild ${currentIndex + 1}`;
    document.getElementById('counter').textContent = `${currentIndex + 1} / ${images.length}`;

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

// Load images on page load
loadImages();
</script>

</body>
</html>
