<?php
// Session starten, falls noch nicht gestartet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php'; // Falls nötig, Pfad anpassen

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

    // Statistiken abfragen
    try {
        // Gesamtzahl der Bilder
        $stmt = $conn->prepare("SELECT COUNT(*) FROM [dbo].[bilder] WHERE [projects-id] = ?");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $statistics['gesamt'] = $stmt->fetchColumn();
        
        // Anzahl der Bilder pro Bewertungsklasse
        for ($i = 1; $i <= 6; $i++) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM [dbo].[bilder] b
                INNER JOIN [dbo].[bewertung] bew ON b.Id = bew.[bilder-id]
                WHERE b.[projects-id] = ? AND bew.strasse = ?
            ");
            $stmt->execute([$_SESSION['PROJEKT_ID'], $i]);
            $statistics['zustand_' . $i] = $stmt->fetchColumn();
        }
        
        // Anzahl der nicht bewerteten Bilder
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM [dbo].[bilder] b
            LEFT JOIN [dbo].[bewertung] bew ON b.Id = bew.[bilder-id]
            WHERE b.[projects-id] = ? AND bew.[bilder-id] IS NULL
        ");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $statistics['nicht_bewertet'] = $stmt->fetchColumn();
        
    } catch (PDOException $e) {
        $statistics['error'] = 'Fehler: ' . htmlspecialchars($e->getMessage());
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
        <table style="width: auto; border-collapse: collapse; margin: 20px 0; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
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
                                    <a href="bewertung/bewertung.php?filter=all" style="color: #007bff; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($statistics['gesamt']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['gesamt']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 1:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_1'] > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=zustand&wert=1" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_1']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_1']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 2:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_2'] > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=zustand&wert=2" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_2']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_2']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 3:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_3'] > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=zustand&wert=3" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_3']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_3']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 4:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_4'] > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=zustand&wert=4" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_4']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_4']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 5:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_5'] > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=zustand&wert=5" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_5']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_5']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 6:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php if ($statistics['zustand_6'] > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=zustand&wert=6" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($statistics['zustand_6']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['zustand_6']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Nicht bewertet:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6; color: #6c757d;">
                                <?php if ($statistics['nicht_bewertet'] > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=nicht_bewertet" style="color: #6c757d; text-decoration: none;"><?= htmlspecialchars($statistics['nicht_bewertet']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($statistics['nicht_bewertet']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
        </table>
    <?php endif; ?>
<?php elseif (is_string($aktuellesProjekt) && !empty($aktuellesProjekt)): ?>
    <p><?= $aktuellesProjekt ?></p>
<?php else: ?>
    <p>Kein Projekt ausgewählt.</p>
<?php endif; ?>

<!-- Button immer unten -->
<?php if (!empty($_SESSION['PROJEKT_ID'])): ?>
    <div style="margin-top:20px;">
        <a href="bewertung/bewertung.php" class="button-link">Zur Bewertung</a>
    </div>
<?php endif; ?>
