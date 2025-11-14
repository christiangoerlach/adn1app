<?php
// Session starten, falls noch nicht gestartet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pfad zu db.php relativ zum Root-Verzeichnis bestimmen
$dbPath = __DIR__ . '/db.php';
if (!file_exists($dbPath)) {
    // Fallback: Versuche von php/view/abschnitt/ aus
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

    // Statistiken abfragen - Straßenabschnitte
    try {
        $projektId = $_SESSION['PROJEKT_ID'];
        
        // Alle Abschnitts-Statistiken in EINER Abfrage mit GROUP BY
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
        <!-- Straßenabschnitte-Spalte -->
        <div style="margin-top: 20px; max-width: 600px;">
            <h3 style="margin: 0 0 15px 0; color: #333; font-size: 1.3rem;">Straßenabschnitte</h3>
            <table style="width: auto; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Kategorie</th>
                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Anzahl</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Gesamtzahl der Abschnitte:</td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #007bff;">
                            <?php 
                            $gesamtAbschnitte = $statistics['straßenabschnitte']['gesamt'] ?? 0;
                            ?>
                            <?php if ($gesamtAbschnitte > 0 && $statistics['straßenabschnitte']['gesamt_first_id']): ?>
                                <a href="/bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['gesamt_first_id']) ?>" style="color: #007bff; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($gesamtAbschnitte) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($gesamtAbschnitte) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 1:</td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                            <?php 
                            $count = $statistics['straßenabschnitte']['zustand_1'] ?? 0;
                            ?>
                            <?php if ($count > 0 && $statistics['straßenabschnitte']['zustand_1_first_id']): ?>
                                <a href="/bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_1_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($count) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 2:</td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                            <?php 
                            $count = $statistics['straßenabschnitte']['zustand_2'] ?? 0;
                            ?>
                            <?php if ($count > 0 && $statistics['straßenabschnitte']['zustand_2_first_id']): ?>
                                <a href="/bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_2_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($count) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 3:</td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                            <?php 
                            $count = $statistics['straßenabschnitte']['zustand_3'] ?? 0;
                            ?>
                            <?php if ($count > 0 && $statistics['straßenabschnitte']['zustand_3_first_id']): ?>
                                <a href="/bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_3_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($count) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 4:</td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                            <?php 
                            $count = $statistics['straßenabschnitte']['zustand_4'] ?? 0;
                            ?>
                            <?php if ($count > 0 && $statistics['straßenabschnitte']['zustand_4_first_id']): ?>
                                <a href="/bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_4_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($count) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 5:</td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                            <?php 
                            $count = $statistics['straßenabschnitte']['zustand_5'] ?? 0;
                            ?>
                            <?php if ($count > 0 && $statistics['straßenabschnitte']['zustand_5_first_id']): ?>
                                <a href="/bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_5_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($count) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 6:</td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                            <?php 
                            $count = $statistics['straßenabschnitte']['zustand_6'] ?? 0;
                            ?>
                            <?php if ($count > 0 && $statistics['straßenabschnitte']['zustand_6_first_id']): ?>
                                <a href="/bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_6_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($count) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Nicht bewertet:</td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6; color: #6c757d;">
                            <?php 
                            $count = $statistics['straßenabschnitte']['nicht_bewertet'] ?? 0;
                            ?>
                            <?php if ($count > 0 && $statistics['straßenabschnitte']['nicht_bewertet_first_id']): ?>
                                <a href="/bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['nicht_bewertet_first_id']) ?>" style="color: #6c757d; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($count) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php elseif (is_string($aktuellesProjekt) && !empty($aktuellesProjekt)): ?>
    <p><?= $aktuellesProjekt ?></p>
<?php else: ?>
    <p>Kein Projekt ausgewählt.</p>
<?php endif; ?>


