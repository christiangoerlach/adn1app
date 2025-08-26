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
        
        // Statistiken für Straßenabschnitte (aus dbo.abschnitte)
        // Gesamtzahl der Abschnitte für das Projekt und erste ID für Link
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM [dbo].[abschnitte] 
            WHERE [projects-id] = ?
        ");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $statistics['straßenabschnitte']['gesamt'] = $stmt->fetchColumn();
        
        // Erste Abschnitts-ID für "Gesamtzahl" Link ermitteln
        $stmt = $conn->prepare("
            SELECT [Id] 
            FROM [dbo].[abschnitte] 
            WHERE [projects-id] = ?
            ORDER BY [Id]
        ");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $firstId = $stmt->fetchColumn();
        $statistics['straßenabschnitte']['gesamt_first_id'] = $firstId ?: null;
        
        // Anzahl der Abschnitte pro Bewertungsklasse (strasse) und erste ID für Links
        for ($i = 1; $i <= 6; $i++) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM [dbo].[abschnitte] 
                WHERE [projects-id] = ? AND [strasse] = ?
            ");
            $stmt->execute([$_SESSION['PROJEKT_ID'], $i]);
            $statistics['straßenabschnitte']['zustand_' . $i] = $stmt->fetchColumn();
            
            // Erste Abschnitts-ID für Link ermitteln
            $stmt = $conn->prepare("
                SELECT [Id] 
                FROM [dbo].[abschnitte] 
                WHERE [projects-id] = ? AND [strasse] = ?
                ORDER BY [Id]
            ");
            $stmt->execute([$_SESSION['PROJEKT_ID'], $i]);
            $firstId = $stmt->fetchColumn();
            $statistics['straßenabschnitte']['zustand_' . $i . '_first_id'] = $firstId ?: null;
        }
        
        // Anzahl der nicht bewerteten Abschnitte (strasse = NULL) und erste ID für Link
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM [dbo].[abschnitte] 
            WHERE [projects-id] = ? AND [strasse] IS NULL
        ");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $statistics['straßenabschnitte']['nicht_bewertet'] = $stmt->fetchColumn();
        
        // Erste Abschnitts-ID für "Nicht bewertet" Link ermitteln
        $stmt = $conn->prepare("
            SELECT [Id] 
            FROM [dbo].[abschnitte] 
            WHERE [projects-id] = ? AND [strasse] IS NULL
            ORDER BY [Id]
        ");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $firstId = $stmt->fetchColumn();
        $statistics['straßenabschnitte']['nicht_bewertet_first_id'] = $firstId ?: null;
        
        // Netzknoten-Zuordnung Statistiken
        // Anzahl der Bilder mit abschnitte-id = NULL (nicht zugeordnet)
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM [dbo].[bilder] 
            WHERE [projects-id] = ? AND [abschnitte-id] IS NULL
        ");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $statistics['netzknoten']['nicht_zugeordnet'] = $stmt->fetchColumn();
        
        // Anzahl der Bilder mit abschnitte-id <> NULL (zugeordnet)
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM [dbo].[bilder] 
            WHERE [projects-id] = ? AND [abschnitte-id] IS NOT NULL
        ");
        $stmt->execute([$_SESSION['PROJEKT_ID']]);
        $statistics['netzknoten']['zugeordnet'] = $stmt->fetchColumn();
        
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
        <!-- Drei-Spalten-Layout -->
        <div style="display: flex; gap: 15px; margin-top: 20px; justify-content: flex-start; align-items: flex-start;">
            <!-- Linke Spalte: Straßenbewertung -->
            <div style="flex: 1;">
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
            </div>
            
            <!-- Rechte Spalte: Netzknoten -->
            <div style="flex: 1;">
                <h3 style="margin: 0 0 15px 0; color: #333; font-size: 1.3rem;">Netzknoten</h3>
                
                <!-- Netzknoten Tabelle -->
                <table style="width: auto; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Modell</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Kein Modell hochgeladen</td>
                        </tr>
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
                                    <a href="bewertung/bewertung.php?filter=zugeordnet" style="color: #007bff; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($zugeordnet) ?></a>
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
                                    <a href="bewertung/bewertung.php?filter=nicht_zugeordnet" style="color: #6c757d; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($nichtZugeordnet) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($nichtZugeordnet) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Dritte Spalte: Straßenabschnitte -->
            <div style="flex: 1;">
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
                                    <a href="bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['gesamt_first_id']) ?>" style="color: #007bff; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($gesamtAbschnitte) ?></a>
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
                                    <a href="bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_1_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
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
                                    <a href="bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_2_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
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
                                    <a href="bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_3_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
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
                                    <a href="bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_4_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
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
                                    <a href="bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_5_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
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
                                    <a href="bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['zustand_6_first_id']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
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
                                    <a href="bewertung/abschnitt-bewertung.php?abschnittId=<?= htmlspecialchars($statistics['straßenabschnitte']['nicht_bewertet_first_id']) ?>" style="color: #6c757d; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($count) ?>
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


