# ADN StraßenWeb - MVC-Architektur

## Überblick

ADN StraßenWeb ist eine Webanwendung zur Verwaltung und Bewertung von Straßenprojekten. Die Anwendung wurde nach dem MVC-Prinzip (Model-View-Controller) restrukturiert und verwendet moderne PHP-Praktiken.

## Projektstruktur

```
adn1app/
├── public/                    # Öffentlich zugängliche Dateien
│   ├── index.php             # Haupteinstiegspunkt (Routing)
│   ├── .htaccess             # URL-Rewriting und Sicherheit
│   ├── css/                  # Stylesheets
│   │   ├── main.css         # Hauptstile
│   │   └── bewertung.css    # Bewertungsseite-Stile
│   ├── js/                   # JavaScript-Dateien
│   │   └── project.js       # Projektseite-Funktionalität
│   └── images/               # Bilder und Grafiken
├── php/                      # Hauptanwendungscode (nicht öffentlich)
│   ├── controller/           # Controller
│   │   ├── ProjectController.php
│   │   ├── BewertungController.php
│   │   └── MapController.php
│   ├── model/                # Modelle
│   │   ├── Project.php      # Projekt-Datenbanklogik
│   │   └── Image.php        # Bild-Datenbanklogik
│   ├── view/                 # Templates
│   │   ├── project/         # Projekt-Templates
│   │   ├── bewertung/       # Bewertungs-Templates
│   │   └── map/             # Karten-Templates
│   └── lib/                  # Hilfsfunktionen
├── config/                   # Konfigurationsdateien
│   ├── app.php              # Allgemeine App-Konfiguration
│   └── database.php         # Datenbankkonfiguration
├── vendor/                   # Composer-Abhängigkeiten
├── database/                 # Datenbankdateien
├── backup/                   # Backup der alten Dateien
└── .env                      # Umgebungsvariablen (nicht im Git)
```

## Installation

### Voraussetzungen

- PHP 7.4 oder höher
- SQL Server (für die Datenbank)
- Composer
- Apache mit mod_rewrite aktiviert

### Schritte

1. **Repository klonen**
   ```bash
   git clone [repository-url]
   cd adn1app
   ```

2. **Abhängigkeiten installieren**
   ```bash
   composer install
   ```

3. **Umgebungsvariablen konfigurieren**
   ```bash
   cp .env.example .env
   # .env-Datei mit Ihren Werten bearbeiten
   ```

4. **Webserver konfigurieren**
   - Document Root auf den `public/` Ordner setzen
   - mod_rewrite aktivieren

5. **Datenbank einrichten**
   - SQL Server-Datenbank erstellen
   - Tabellen aus dem `database/` Ordner importieren

## Konfiguration

### Umgebungsvariablen (.env)

```env
# Datenbank
DB_SERVER=localhost
DB_NAME=adn_db
DB_USER=username
DB_PASS=password

# Azure
AZURE_MAPS_KEY=your_azure_maps_key
AZURE_STORAGE_CONNECTION_STRING=your_connection_string

# App
APP_DEBUG=false
```

### Webserver-Konfiguration

#### Apache (.htaccess bereits konfiguriert)
- mod_rewrite muss aktiviert sein
- Alle Anfragen werden an `public/index.php` weitergeleitet

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Verwendung

### Routen

- `/` - Hauptseite mit Projektauswahl
- `/bewertung` - Bildbewertungsseite
- `/map` - Azure Maps-Kartenansicht
- `/map-old` - Alte Kartenansicht
- `/api/*` - API-Endpunkte

### API-Endpunkte

- `GET /api/project-overview` - Projektübersicht laden
- `POST /api/rate-image` - Bild bewerten
- `GET /api/next-image` - Nächstes Bild
- `GET /api/previous-image` - Vorheriges Bild

## Entwicklung

### Code-Struktur

Das Projekt folgt dem MVC-Pattern:

- **Models**: Datenbanklogik und Business-Logik
- **Views**: HTML-Templates und Präsentationslogik
- **Controllers**: Anfrageverarbeitung und Routing

### Neue Funktionen hinzufügen

1. **Model erstellen** in `php/model/`
2. **Controller erstellen** in `php/controller/`
3. **View erstellen** in `php/view/`
4. **Route hinzufügen** in `public/index.php`

### Beispiel: Neuer Controller

```php
<?php
class NewController {
    public function index() {
        // Logik hier
        require_once __DIR__ . '/../view/new/index.php';
    }
}
```

## Sicherheit

- Alle sensiblen Dateien sind außerhalb des öffentlichen Verzeichnisses
- SQL-Injection-Schutz durch Prepared Statements
- XSS-Schutz durch `htmlspecialchars()`
- Session-Sicherheit konfiguriert
- .htaccess verhindert Zugriff auf sensible Dateien

## Wartung

### Backup

- Alte Dateien sind im `backup/` Ordner gesichert
- Datenbank regelmäßig sichern
- .env-Datei sichern (enthält API-Keys)

### Updates

1. Repository aktualisieren
2. `composer update` ausführen
3. Datenbankmigrationen durchführen
4. Cache leeren

## Fehlerbehebung

### Häufige Probleme

1. **404-Fehler**: mod_rewrite aktivieren
2. **Datenbankfehler**: .env-Datei prüfen
3. **Azure Maps funktioniert nicht**: API-Key prüfen

### Logs

- PHP-Fehler werden in den Webserver-Logs gespeichert
- Datenbankfehler werden geloggt
- Debug-Modus in .env aktivieren für detaillierte Fehler

## Lizenz

Proprietär - ADN Consulting

## Support

Bei Fragen oder Problemen wenden Sie sich an das ADN Consulting Team.

