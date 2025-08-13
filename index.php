<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>AJAX Bildergalerie aus Azure Blob</title>
    <style>
        body { text-align: center; font-family: Arial, sans-serif; }
        img { max-width: 80vw; max-height: 80vh; margin: 20px auto; display: block; }
        .nav-buttons { margin: 20px; }
        button { font-size: 1.2rem; padding: 10px 20px; margin: 0 10px; }
    </style>
</head>
<body>

<h1>Bildergalerie mit AJAX</h1>

<img id="mainImage" src="" alt="Bild" />
<div class="nav-buttons">
    <button id="prevBtn">← Zurück</button>
    <span id="counter">0 / 0</span>
    <button id="nextBtn">Vor →</button>
</div>

<script>
let images = [];
let currentIndex = 0;

function preloadImage(url) {
    const img = new Image();
    img.src = url;
}

function updateImage() {
    const img = document.getElementById('mainImage');
    img.src = images[currentIndex];
    img.alt = `Bild ${currentIndex + 1}`;
    document.getElementById('counter').textContent = `${currentIndex + 1} / ${images.length}`;

    // Buttons aktivieren/deaktivieren
    document.getElementById('prevBtn').disabled = currentIndex === 0;
    document.getElementById('nextBtn').disabled = currentIndex === images.length - 1;

    // Preload next two images
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
            images = data;
            if (images.length > 0) {
                updateImage();
            } else {
                document.getElementById('mainImage').alt = 'Keine Bilder gefunden';
            }
        })
        .catch(error => {
            console.error('Fehler beim Laden der Bilder:', error);
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

loadImages();
</script>

<?php
require_once 'db.php'; // Verbindung wird hergestellt

$sql = "SELECT * FROM [dbo].[ImageRegistry]";
$stmt = $conn->query($sql);

echo "<h2>ImageRegistry-Daten:</h2>";
echo "<ul>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<li>" . htmlspecialchars(json_encode($row)) . "</li>";
}
echo "</ul>";
?>


</body>
</html>
