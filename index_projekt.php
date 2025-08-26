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
        
        // Statistiken für Straßenabschnitte (Gehweg/Seitenstreifen)
        $straßenabschnitte_felder = ['gehweg_links', 'gehweg_rechts', 'seitenstreifen_links', 'seitenstreifen_rechts'];
        
        foreach ($straßenabschnitte_felder as $feld) {
            // Anzahl der Bilder pro Bewertungsklasse für dieses Feld
            for ($i = 1; $i <= 6; $i++) {
                $stmt = $conn->prepare("
                    SELECT COUNT(*) 
                    FROM [dbo].[bilder] b
                    INNER JOIN [dbo].[bewertung] bew ON b.Id = bew.[bilder-id]
                    WHERE b.[projects-id] = ? AND bew.[$feld] = ?
                ");
                $stmt->execute([$_SESSION['PROJEKT_ID'], $i]);
                $statistics['straßenabschnitte'][$feld]['zustand_' . $i] = $stmt->fetchColumn();
            }
            
            // Spezielle Werte (0, 9, 10, 11)
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM [dbo].[bilder] b
                INNER JOIN [dbo].[bewertung] bew ON b.Id = bew.[bilder-id]
                WHERE b.[projects-id] = ? AND bew.[$feld] = 0
            ");
            $stmt->execute([$_SESSION['PROJEKT_ID']]);
            $statistics['straßenabschnitte'][$feld]['nicht_bewertet'] = $stmt->fetchColumn();
            
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM [dbo].[bilder] b
                INNER JOIN [dbo].[bewertung] bew ON b.Id = bew.[bilder-id]
                WHERE b.[projects-id] = ? AND bew.[$feld] = 9
            ");
            $stmt->execute([$_SESSION['PROJEKT_ID']]);
            $statistics['straßenabschnitte'][$feld]['ausgeschlossen'] = $stmt->fetchColumn();
            
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM [dbo].[bilder] b
                INNER JOIN [dbo].[bewertung] bew ON b.Id = bew.[bilder-id]
                WHERE b.[projects-id] = ? AND bew.[$feld] = 10
            ");
            $stmt->execute([$_SESSION['PROJEKT_ID']]);
            $statistics['straßenabschnitte'][$feld]['nicht_vorhanden'] = $stmt->fetchColumn();
            
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM [dbo].[bilder] b
                INNER JOIN [dbo].[bewertung] bew ON b.Id = bew.[bilder-id]
                WHERE b.[projects-id] = ? AND bew.[$feld] = 11
            ");
            $stmt->execute([$_SESSION['PROJEKT_ID']]);
            $statistics['straßenabschnitte'][$feld]['wie_strasse'] = $stmt->fetchColumn();
        }
        
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
                                // Berechne Gesamtzahl aller Straßenabschnitte-Bewertungen
                                $gesamtAbschnitte = 0;
                                $straßenabschnitte_felder = ['gehweg_links', 'gehweg_rechts', 'seitenstreifen_links', 'seitenstreifen_rechts'];
                                foreach ($straßenabschnitte_felder as $feld) {
                                    for ($i = 0; $i <= 11; $i++) {
                                        if ($i >= 1 && $i <= 6) {
                                            $gesamtAbschnitte += $statistics['straßenabschnitte'][$feld]['zustand_' . $i] ?? 0;
                                        } elseif ($i === 0) {
                                            $gesamtAbschnitte += $statistics['straßenabschnitte'][$feld]['nicht_bewertet'] ?? 0;
                                        } elseif ($i === 9) {
                                            $gesamtAbschnitte += $statistics['straßenabschnitte'][$feld]['ausgeschlossen'] ?? 0;
                                        } elseif ($i === 10) {
                                            $gesamtAbschnitte += $statistics['straßenabschnitte'][$feld]['nicht_vorhanden'] ?? 0;
                                        } elseif ($i === 11) {
                                            $gesamtAbschnitte += $statistics['straßenabschnitte'][$feld]['wie_strasse'] ?? 0;
                                        }
                                    }
                                }
                                ?>
                                <?php if ($gesamtAbschnitte > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=all" style="color: #007bff; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($gesamtAbschnitte) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($gesamtAbschnitte) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 1:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php 
                                $count = 0;
                                foreach ($straßenabschnitte_felder as $feld) {
                                    $count += $statistics['straßenabschnitte'][$feld]['zustand_1'] ?? 0;
                                }
                                ?>
                                <?php if ($count > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=straßenabschnitte&wert=1" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($count) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 2:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php 
                                $count = 0;
                                foreach ($straßenabschnitte_felder as $feld) {
                                    $count += $statistics['straßenabschnitte'][$feld]['zustand_2'] ?? 0;
                                }
                                ?>
                                <?php if ($count > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=straßenabschnitte&wert=2" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($count) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 3:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php 
                                $count = 0;
                                foreach ($straßenabschnitte_felder as $feld) {
                                    $count += $statistics['straßenabschnitte'][$feld]['zustand_3'] ?? 0;
                                }
                                ?>
                                <?php if ($count > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=straßenabschnitte&wert=3" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($count) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 4:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php 
                                $count = 0;
                                foreach ($straßenabschnitte_felder as $feld) {
                                    $count += $statistics['straßenabschnitte'][$feld]['zustand_4'] ?? 0;
                                }
                                ?>
                                <?php if ($count > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=straßenabschnitte&wert=4" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($count) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 5:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php 
                                $count = 0;
                                foreach ($straßenabschnitte_felder as $feld) {
                                    $count += $statistics['straßenabschnitte'][$feld]['zustand_5'] ?? 0;
                                }
                                ?>
                                <?php if ($count > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=straßenabschnitte&wert=5" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($count) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Zustand 6:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">
                                <?php 
                                $count = 0;
                                foreach ($straßenabschnitte_felder as $feld) {
                                    $count += $statistics['straßenabschnitte'][$feld]['zustand_6'] ?? 0;
                                }
                                ?>
                                <?php if ($count > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=straßenabschnitte&wert=6" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($count) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Nicht bewertet:</td>
                            <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6; color: #6c757d;">
                                <?php 
                                $count = 0;
                                foreach ($straßenabschnitte_felder as $feld) {
                                    $count += $statistics['straßenabschnitte'][$feld]['nicht_bewertet'] ?? 0;
                                }
                                ?>
                                <?php if ($count > 0): ?>
                                    <a href="bewertung/bewertung.php?filter=straßenabschnitte&wert=0" style="color: #6c757d; text-decoration: none;"><?= htmlspecialchars($count) ?></a>
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


