<?php
// Session starten, falls noch nicht gestartet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pfad zu db.php relativ zum Root-Verzeichnis bestimmen
$dbPath = __DIR__ . '/db.php';
if (!file_exists($dbPath)) {
    // Fallback: Versuche von php/view/netzknoten/ aus
    $dbPath = dirname(__DIR__) . '/db.php';
}
require_once $dbPath;

$aktuellesProjekt = '';
$statistics = [];

// Prüfen, ob eine Projekt-ID vorhanden ist
if (!empty($_SESSION['PROJEKT_ID'])) {

    // Projektname abfragen
    try {
        $stmt = $conn->prepare("SELECT Projektname FROM [dbo].[projects] WHERE Id = ?");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $aktuellesProjekt = $row['Projektname'];
        } else {
            $aktuellesProjekt = '(Projekt nicht gefunden)';
        }
    } catch (PDOException $e) {
        $aktuellesProjekt = 'Fehler beim Laden des Projekts: ' . htmlspecialchars($e->getMessage());
    }

    // Statistiken abfragen - Netzknoten
    try {
        $projektId = $_SESSION['PROJEKT_ID'];
        
        // Netzknoten-Statistiken abfragen
        $stmt = $conn->prepare("
            SELECT 
                COUNT(CASE WHEN b.[abschnitte-id] IS NULL THEN 1 END) as nicht_zugeordnet,
                COUNT(CASE WHEN b.[abschnitte-id] IS NOT NULL THEN 1 END) as zugeordnet
            FROM [dbo].[bilder] b
            WHERE b.[projects-id] = ?
        ");
        $stmt->execute([$projektId]);
        $bildStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bildStats) {
            $statistics['netzknoten']['nicht_zugeordnet'] = (int)($bildStats['nicht_zugeordnet'] ?? 0);
            $statistics['netzknoten']['zugeordnet'] = (int)($bildStats['zugeordnet'] ?? 0);
        } else {
            $statistics['netzknoten']['nicht_zugeordnet'] = 0;
            $statistics['netzknoten']['zugeordnet'] = 0;
        }
        
    } catch (PDOException $e) {
        $statistics['error'] = 'Fehler: ' . htmlspecialchars($e->getMessage());
        error_log('Fehler beim Laden der Statistiken: ' . $e->getMessage());
    }

} else {
    $aktuellesProjekt = null; // kein Projekt ausgewählt
}
?>

<!-- Anzeige -->
<?php if (!empty($_SESSION['PROJEKT_ID']) && $aktuellesProjekt): ?>
    <p>Aktuelles Projekt: <strong><?= htmlspecialchars($aktuellesProjekt) ?></strong></p>
    
    <?php if (isset($statistics['error'])): ?>
        <p style="color: red;"><?= $statistics['error'] ?></p>
    <?php else: ?>
        <!-- Netzknoten-Spalte -->
        <div style="margin-top: 20px; max-width: 600px;">
            <h3 style="margin: 0 0 15px 0; color: #333; font-size: 1.3rem;">Netzknoten</h3>
            
            <!-- Netzknoten Tabelle -->
            <table style="width: auto; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Modell</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $geojsonDir = __DIR__ . '/zuordnung/geojson/';
                    $geojsonFiles = [];
                    
                    if (is_dir($geojsonDir)) {
                        $files = scandir($geojsonDir);
                        foreach ($files as $file) {
                            if (pathinfo($file, PATHINFO_EXTENSION) === 'geojson') {
                                $geojsonFiles[] = $file;
                            }
                        }
                    }
                    
                    if (empty($geojsonFiles)): ?>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Keine Datei vorhanden</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($geojsonFiles as $file): ?>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">
                                    <a href="#" onclick="openGeoJSONPopup('<?= htmlspecialchars($file) ?>')" style="color: #007bff; text-decoration: none; font-weight: 500;">
                                        <?= htmlspecialchars($file) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Zuordnung Netzknoten Tabelle -->
            <table style="width: auto; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Zuordnung Netzknoten</th>
                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Anzahl</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zugeordnet</td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #007bff;">
                            <?php 
                            $zugeordnet = $statistics['netzknoten']['zugeordnet'] ?? 0;
                            if ($zugeordnet > 0): 
                            ?>
                                <a href="/bewertung/bewertung.php?filter=zugeordnet" style="color: #007bff; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($zugeordnet) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($zugeordnet) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Nicht zugeordnet</td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #6c757d;">
                            <?php 
                            $nichtZugeordnet = $statistics['netzknoten']['nicht_zugeordnet'] ?? 0;
                            if ($nichtZugeordnet > 0): 
                            ?>
                                <a href="/bewertung/bewertung.php?filter=nicht_zugeordnet" style="color: #6c757d; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($nichtZugeordnet) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($nichtZugeordnet) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- GeoJSON Popup wird aus zuordnung/geojson_popup.html geladen -->
        <iframe id="geojsonPopupFrame" src="../zuordnung/geojson_popup.html" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; border: none;"></iframe>
        
        <script>
        function openGeoJSONPopup(filename) {
            const frame = document.getElementById('geojsonPopupFrame');
            frame.style.display = 'block';
            
            // Warten bis das iframe geladen ist, dann die Funktion aufrufen
            frame.onload = function() {
                frame.contentWindow.openGeoJSONPopup(filename);
            };
            
            // Falls das iframe bereits geladen ist
            if (frame.contentWindow.openGeoJSONPopup) {
                frame.contentWindow.openGeoJSONPopup(filename);
            }
        }
        
        // Event-Listener für Schließen-Event vom iframe
        window.addEventListener('message', function(event) {
            if (event.data === 'closeGeoJSONPopup') {
                document.getElementById('geojsonPopupFrame').style.display = 'none';
            }
        });
        </script>
    <?php endif; ?>
<?php elseif (is_string($aktuellesProjekt) && !empty($aktuellesProjekt)): ?>
    <p><?= $aktuellesProjekt ?></p>
<?php else: ?>
    <p>Kein Projekt ausgewählt.</p>
<?php endif; ?>


