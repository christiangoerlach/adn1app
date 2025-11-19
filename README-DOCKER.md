# Docker-Setup für lokale Entwicklung (Azure-ähnlich)

Dieses Docker-Setup bildet die Azure App Service Umgebung nach, um konsistente Ergebnisse zwischen lokaler Entwicklung und Azure zu gewährleisten.

## Voraussetzungen

- Docker Desktop (Windows/Mac) oder Docker Engine + Docker Compose (Linux)
- Mindestens 4 GB RAM verfügbar
- Ports 8000 und 1433 müssen frei sein

## Schnellstart

### 1. Umgebungsvariablen konfigurieren

Die `.env` Datei wird automatisch geladen. Stelle sicher, dass sie die Azure-Datenbankanmeldedaten enthält:

```env
# Datenbank (Azure SQL Database)
DB_SERVER=tcp:adndb.database.windows.net,1433
DB_NAME=adn_db
DB_USER=adn_db
DB_PASS=dein_passwort

# Anwendung
APP_DEBUG=true

# Azure Storage
AZURE_STORAGE_CONNECTION_STRING=dein_connection_string
AZURE_MAPS_KEY=dein_maps_key
```

**Wichtig:** Stelle sicher, dass deine IP-Adresse in den Azure SQL Firewall-Regeln erlaubt ist!

### 2. Docker-Container starten

```bash
docker-compose up -d
```

Dies startet:
- **nginx** (Port 8000) - Webserver wie in Azure
- **PHP 8.2-FPM** - PHP-Prozess wie in Azure
- **Azure SQL Database** - Verwendet die gleiche Datenbank wie Azure (aus .env)

**Hinweis:** Der lokale SQL Server Container ist standardmäßig deaktiviert. Um ihn zu aktivieren, siehe Abschnitt "Lokale Datenbank verwenden".

### 3. Composer Dependencies installieren (falls nötig)

```bash
docker-compose exec web composer install
```

### 4. Anwendung aufrufen

Öffne im Browser: **http://localhost:8000**

## Wichtige Befehle

### Container stoppen
```bash
docker-compose down
```

### Container stoppen und Volumes löschen
```bash
docker-compose down -v
```

### Logs anzeigen
```bash
docker-compose logs -f
```

### Logs nur für einen Service
```bash
docker-compose logs -f nginx
docker-compose logs -f web
docker-compose logs -f db
```

### In PHP-Container einloggen
```bash
docker-compose exec web bash
```

### In nginx-Container einloggen
```bash
docker-compose exec nginx sh
```

### Composer-Befehle ausführen
```bash
docker-compose exec web composer update
docker-compose exec web composer require package/name
```

### PHP-Befehle ausführen
```bash
docker-compose exec web php -v
docker-compose exec web php -m  # Zeigt installierte Extensions
```

## Unterschiede zu Azure

### Was ist identisch:
- ✅ nginx als Webserver
- ✅ PHP 8.2-FPM
- ✅ Gleiche nginx-Konfiguration (Routing, Sicherheit)
- ✅ Gleiche PHP-Einstellungen (Memory, Timeout, etc.)
- ✅ **Gleiche Azure SQL Database** - Verwendet exakt die gleiche Datenbank wie Azure

### Was unterscheidet sich:
- ⚠️ **HTTPS**: Lokal HTTP, Azure HTTPS (kann mit Reverse Proxy ergänzt werden)
- ⚠️ **Azure-spezifische Header**: `HTTP_X_MS_CLIENT_PRINCIPAL_NAME` wird lokal nicht gesetzt
- ⚠️ **Dateisystem**: Lokal Volume-Mount, Azure Azure Storage (kann konfiguriert werden)

## Azure SQL Firewall konfigurieren

Damit Docker auf die Azure-Datenbank zugreifen kann, muss deine IP-Adresse in den Firewall-Regeln erlaubt sein:

1. Gehe zu **Azure Portal → SQL Server → Networking**
2. Unter **Public network access** wähle "Selected networks"
3. Klicke auf **+ Add client IP** oder füge deine IP-Adresse manuell hinzu
4. Speichere die Änderungen

**Alternative:** Für Entwicklung kannst du temporär "Allow Azure services and resources to access this server" aktivieren (weniger sicher).

## Lokale Datenbank verwenden (Optional)

Falls du einen lokalen SQL Server Container verwenden möchtest statt der Azure-Datenbank:

1. Entferne die Kommentare in `docker-compose.yml` für den `db` Service
2. Füge `depends_on: - db` zum `web` Service hinzu
3. Setze in `web` environment: `DB_SERVER=db`
4. Starte neu: `docker-compose up -d`

## Troubleshooting

### Port bereits belegt
Falls Port 8000 oder 1433 belegt ist, ändere die Ports in `docker-compose.yml`:

```yaml
nginx:
  ports:
    - "8080:80"  # Statt 8000

db:
  ports:
    - "1434:1433"  # Statt 1433
```

### Datenbank-Verbindungsfehler

**Bei Azure-Datenbank:**
1. Prüfe ob deine IP-Adresse in Azure SQL Firewall erlaubt ist
2. Prüfe die Anmeldedaten in der `.env` Datei
3. Teste die Verbindung: `docker-compose exec web php -r "require '/var/www/html/config/database.php'; echo 'OK';"`
4. Prüfe Logs: `docker-compose logs web`

**Bei lokaler Datenbank:**
1. Prüfe ob SQL Server Container läuft: `docker-compose ps`
2. Prüfe Logs: `docker-compose logs db`
3. Warte 10-15 Sekunden nach Start, SQL Server braucht Zeit zum Initialisieren

### PHP-Extensions fehlen
Falls eine Extension fehlt, bearbeite `docker/Dockerfile` und baue neu:

```bash
docker-compose build web
docker-compose up -d
```

### Dateiberechtigungen
Falls es Probleme mit Dateiberechtigungen gibt:

```bash
docker-compose exec web chown -R www-data:www-data /var/www/html
```

### nginx-Konfiguration testen
```bash
docker-compose exec nginx nginx -t
```

### nginx neu laden (ohne Container-Neustart)
```bash
docker-compose exec nginx nginx -s reload
```

## Entwicklung

### Code-Änderungen
Code-Änderungen werden sofort übernommen (Volume-Mount). Nur bei Änderungen an:
- `docker-compose.yml`
- `docker/Dockerfile`
- `docker/nginx.conf`

müssen die Container neu gebaut werden:

```bash
docker-compose build
docker-compose up -d
```

### Datenbank-Zugriff von außen
Die SQL Server Datenbank ist von deinem Host-System erreichbar:
- **Host**: `localhost`
- **Port**: `1433`
- **User**: `sa`
- **Password**: `YourStrong@Passw0rd` (oder aus `.env`)

Du kannst Tools wie Azure Data Studio, SQL Server Management Studio oder DBeaver verwenden.

## Performance-Tipps

1. **Docker Desktop Ressourcen**: Stelle sicher, dass Docker Desktop genug RAM/CPU zugewiesen hat
2. **Volume-Mounts**: Auf Windows/Mac können Volume-Mounts langsam sein. Für bessere Performance nutze `cached` oder `delegated`:
   ```yaml
   volumes:
     - .:/var/www/html:cached
   ```

## Vergleich: Lokal vs. Azure

| Feature | Lokal (Docker) | Azure App Service |
|---------|---------------|-------------------|
| Webserver | nginx | nginx |
| PHP | 8.2-FPM | 8.2-FPM |
| Routing | nginx try_files | nginx try_files |
| HTTPS | ❌ (kann ergänzt werden) | ✅ |
| SQL Server | ✅ (Container) | ✅ (Azure SQL) |
| .htaccess | ❌ (nginx) | ❌ (nginx) |
| Umgebungsvariablen | .env | App Settings |

## Nächste Schritte

1. Teste deine Anwendung unter http://localhost:8000
2. Vergleiche das Verhalten mit Azure
3. Bei Unterschieden: Prüfe Logs und Konfigurationen



