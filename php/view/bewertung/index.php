<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>Bildergalerie Bewertung</title>
    <link rel="stylesheet" href="/css/bewertung.css">
</head>
<body onload="InitBewertung()">
    <div class="container">
        <div class="left-panel">
            <img id="mainImage" src="" alt="Hauptbild" />
        </div>
        <div class="right-panel">
            <div class="project-name"><?= htmlspecialchars($project['Projektname']) ?></div>
            <div class="counter-row">
                <span id="currentImageIndex">1</span> / <span id="totalImages"><?= count($images) ?></span>
            </div>
            
            <div class="nav-buttons">
                <button class="nav-arrow" id="prevBtn" onclick="previousImage()">‹</button>
                <button class="nav-arrow" id="nextBtn" onclick="nextImage()">›</button>
            </div>
            
            <div class="rating-section">
                <h2>Bewertung</h2>
                <div class="rating-btns">
                    <button class="rating-btn" data-rating="1" onclick="rateImage(1)">1 - Sehr schlecht</button>
                    <button class="rating-btn" data-rating="2" onclick="rateImage(2)">2 - Schlecht</button>
                    <button class="rating-btn" data-rating="3" onclick="rateImage(3)">3 - Mittelmäßig</button>
                    <button class="rating-btn" data-rating="4" onclick="rateImage(4)">4 - Gut</button>
                    <button class="rating-btn" data-rating="5" onclick="rateImage(5)">5 - Sehr gut</button>
                </div>
            </div>
            
            <div class="rating-section">
                <h2>Status</h2>
                <div id="ratingStatus">Keine Bewertung</div>
            </div>
            
            <div class="rating-section">
                <h2>Navigation</h2>
                <button class="nav-btn" onclick="goToFirst()">Erstes Bild</button>
                <button class="nav-btn" onclick="goToLast()">Letztes Bild</button>
            </div>
        </div>
    </div>

    <script>
        // Bilderdaten an JavaScript übergeben
        const images = <?= json_encode($images) ?>;
        let currentImageIndex = 0;
        let currentImage = images[0];
        
        function InitBewertung() {
            if (images.length > 0) {
                displayImage(currentImage);
                updateNavigation();
            }
        }
        
        function displayImage(image) {
            if (!image) return;
            
            document.getElementById('mainImage').src = image.BildURL || 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIGltYWdlPC90ZXh0Pjwvc3ZnPg==';
            document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;
            document.getElementById('totalImages').textContent = images.length;
            
            // Bewertungsstatus anzeigen
            const statusElement = document.getElementById('ratingStatus');
            if (image.Bewertung) {
                statusElement.textContent = `Bewertet: ${image.Bewertung}/5`;
                statusElement.className = 'rated';
            } else {
                statusElement.textContent = 'Keine Bewertung';
                statusElement.className = 'not-rated';
            }
            
            // Bewertungsbuttons aktualisieren
            updateRatingButtons(image.Bewertung);
        }
        
        function updateRatingButtons(currentRating) {
            const buttons = document.querySelectorAll('.rating-btn');
            buttons.forEach(btn => {
                const rating = parseInt(btn.dataset.rating);
                if (rating === currentRating) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }
        
        function updateNavigation() {
            document.getElementById('prevBtn').disabled = currentImageIndex === 0;
            document.getElementById('nextBtn').disabled = currentImageIndex === images.length - 1;
        }
        
        function nextImage() {
            if (currentImageIndex < images.length - 1) {
                currentImageIndex++;
                currentImage = images[currentImageIndex];
                displayImage(currentImage);
                updateNavigation();
            }
        }
        
        function previousImage() {
            if (currentImageIndex > 0) {
                currentImageIndex--;
                currentImage = images[currentImageIndex];
                displayImage(currentImage);
                updateNavigation();
            }
        }
        
        function goToFirst() {
            currentImageIndex = 0;
            currentImage = images[0];
            displayImage(currentImage);
            updateNavigation();
        }
        
        function goToLast() {
            currentImageIndex = images.length - 1;
            currentImage = images[currentImageIndex];
            displayImage(currentImage);
            updateNavigation();
        }
        
        function rateImage(rating) {
            if (!currentImage) return;
            
            // Bewertung an Server senden
            fetch('/api/rate-image', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `image_id=${currentImage.Id}&rating=${rating}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Bewertung lokal aktualisieren
                    currentImage.Bewertung = rating;
                    displayImage(currentImage);
                    
                    // Automatisch zum nächsten Bild
                    setTimeout(() => {
                        if (currentImageIndex < images.length - 1) {
                            nextImage();
                        }
                    }, 500);
                } else {
                    alert('Fehler beim Speichern der Bewertung: ' + (data.error || 'Unbekannter Fehler'));
                }
            })
            .catch(error => {
                console.error('Fehler:', error);
                alert('Fehler beim Speichern der Bewertung');
            });
        }
        
        // Tastatur-Navigation
        document.addEventListener('keydown', function(e) {
            switch(e.key) {
                case 'ArrowLeft':
                    previousImage();
                    break;
                case 'ArrowRight':
                    nextImage();
                    break;
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                    rateImage(parseInt(e.key));
                    break;
            }
        });
    </script>
</body>
</html>

