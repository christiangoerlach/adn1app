<?php
// Session starten, falls noch nicht gestartet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pfad zu db.php relativ zum Root-Verzeichnis bestimmen
$dbPath = __DIR__ . '/db.php';
if (!file_exists($dbPath)) {
    // Fallback: Versuche von php/view/project/ aus
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

    // Statistiken abfragen - OPTIMIERT: Kombinierte Abfragen statt viele einzelne
    try {
        $projektId = $_SESSION['PROJEKT_ID'];
        
        // 1. Alle Bild-Statistiken in EINER Abfrage mit CASE WHEN
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as gesamt,
                COUNT(CASE WHEN bew.[bilder-id] IS NULL THEN 1 END) as nicht_bewertet,
                COUNT(CASE WHEN bew.strasse = 1 THEN 1 END) as zustand_1,
                COUNT(CASE WHEN bew.strasse = 2 THEN 1 END) as zustand_2,
                COUNT(CASE WHEN bew.strasse = 3 THEN 1 END) as zustand_3,
                COUNT(CASE WHEN bew.strasse = 4 THEN 1 END) as zustand_4,
                COUNT(CASE WHEN bew.strasse = 5 THEN 1 END) as zustand_5,
                COUNT(CASE WHEN bew.strasse = 6 THEN 1 END) as zustand_6,
                COUNT(CASE WHEN b.[abschnitte-id] IS NULL THEN 1 END) as nicht_zugeordnet,
                COUNT(CASE WHEN b.[abschnitte-id] IS NOT NULL THEN 1 END) as zugeordnet
            FROM [dbo].[bilder] b
            LEFT JOIN [dbo].[bewertung] bew ON b.Id = bew.[bilder-id]
            WHERE b.[projects-id] = ?
        ");
        $stmt->execute([$projektId]);
        $bildStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bildStats) {
            $statistics['gesamt'] = (int)($bildStats['gesamt'] ?? 0);
            $statistics['nicht_bewertet'] = (int)($bildStats['nicht_bewertet'] ?? 0);
            $statistics['zustand_1'] = (int)($bildStats['zustand_1'] ?? 0);
            $statistics['zustand_2'] = (int)($bildStats['zustand_2'] ?? 0);
            $statistics['zustand_3'] = (int)($bildStats['zustand_3'] ?? 0);
            $statistics['zustand_4'] = (int)($bildStats['zustand_4'] ?? 0);
            $statistics['zustand_5'] = (int)($bildStats['zustand_5'] ?? 0);
            $statistics['zustand_6'] = (int)($bildStats['zustand_6'] ?? 0);
            $statistics['netzknoten']['nicht_zugeordnet'] = (int)($bildStats['nicht_zugeordnet'] ?? 0);
            $statistics['netzknoten']['zugeordnet'] = (int)($bildStats['zugeordnet'] ?? 0);
        } else {
            // Fallback-Werte
            $statistics['gesamt'] = 0;
            $statistics['nicht_bewertet'] = 0;
            for ($i = 1; $i <= 6; $i++) {
                $statistics['zustand_' . $i] = 0;
            }
            $statistics['netzknoten']['nicht_zugeordnet'] = 0;
            $statistics['netzknoten']['zugeordnet'] = 0;
        }
        
        // 2. Alle Abschnitts-Statistiken in EINER Abfrage mit GROUP BY
        $stmt = $conn->prepare("
            SELECT 
                strasse,
                COUNT(*) as anzahl,
                MIN([Id]) as erste_id
            FROM [dbo].[abschnitte]
            WHERE [projects-id] = ?
            GROUP BY strasse
        ");
        $stmt->execute([$projektId]);
        $abschnittStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Initialisiere alle Werte
        $statistics['straßenabschnitte']['gesamt'] = 0;
        $statistics['straßenabschnitte']['gesamt_first_id'] = null;
        $statistics['straßenabschnitte']['nicht_bewertet'] = 0;
        $statistics['straßenabschnitte']['nicht_bewertet_first_id'] = null;
        
        // Initialisiere Zustände 1-6
        for ($i = 1; $i <= 6; $i++) {
            $statistics['straßenabschnitte']['zustand_' . $i] = 0;
            $statistics['straßenabschnitte']['zustand_' . $i . '_first_id'] = null;
        }
        
        // Verarbeite die gruppierten Ergebnisse
        $minId = null;
        foreach ($abschnittStats as $row) {
            $strasse = $row['strasse'];
            $anzahl = (int)$row['anzahl'];
            $ersteId = $row['erste_id'];
            
            $statistics['straßenabschnitte']['gesamt'] += $anzahl;
            
            // Erste Gesamt-ID (kleinste ID überhaupt)
            if ($minId === null || $ersteId < $minId) {
                $minId = $ersteId;
            }
            
            if ($strasse === null) {
                $statistics['straßenabschnitte']['nicht_bewertet'] = $anzahl;
                $statistics['straßenabschnitte']['nicht_bewertet_first_id'] = $ersteId;
            } elseif ($strasse >= 1 && $strasse <= 6) {
                $statistics['straßenabschnitte']['zustand_' . $strasse] = $anzahl;
                $statistics['straßenabschnitte']['zustand_' . $strasse . '_first_id'] = $ersteId;
            }
        }
        
        // Setze die erste Gesamt-ID
        if ($minId !== null) {
            $statistics['straßenabschnitte']['gesamt_first_id'] = $minId;
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
        <!-- Ein-Spalten-Layout -->
        <div style="margin-top: 20px;">
            <!-- Spalte: Straßenbewertung -->
            <div style="max-width: 600px;">
                <h3 style="margin: 0 0 15px 0; color: #333; font-size: 1.3rem;">Straßenbewertung</h3>
                <table style="width: auto; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Kategorie</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Anzahl</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Gesamtzahl der Bilder:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #007bff;">
                                <?php if ($statistics['gesamt'] > 0): ?>
                                    <a href="/bewertung/bewertung.php?filter=all" style="color: #007bff; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($statistics['gesamt']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['gesamt']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 1:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_1'] > 0): ?>
                                    <a href="/bewertung/bewertung.php?filter=zustand&wert=1" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_1']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_1']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 2:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_2'] > 0): ?>
                                    <a href="/bewertung/bewertung.php?filter=zustand&wert=2" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_2']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_2']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 3:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_3'] > 0): ?>
                                    <a href="/bewertung/bewertung.php?filter=zustand&wert=3" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_3']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_3']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 4:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_4'] > 0): ?>
                                    <a href="/bewertung/bewertung.php?filter=zustand&wert=4" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_4']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_4']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 5:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_5'] > 0): ?>
                                    <a href="/bewertung/bewertung.php?filter=zustand&wert=5" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_5']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_5']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 6:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_6'] > 0): ?>
                                    <a href="/bewertung/bewertung.php?filter=zustand&wert=6" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_6']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_6']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Nicht bewertet:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6; color: #6c757d;">
                                <?php if ($statistics['nicht_bewertet'] > 0): ?>
                                    <a href="/bewertung/bewertung.php?filter=nicht_bewertet" style="color: #6c757d; text-decoration: none;"><?= htmlspecialchars($statistics['nicht_bewertet']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['nicht_bewertet']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
     <?php endif; ?>
 <?php elseif (is_string($aktuellesProjekt) && !empty($aktuellesProjekt)): ?>
     <p><?= $aktuellesProjekt ?></p>
 <?php else: ?>
     <p>Kein Projekt ausgewählt.</p>
 <?php endif; ?>


