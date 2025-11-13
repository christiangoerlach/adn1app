# Lokaler Aufruf der Anwendung

## Schnellstart

### Windows:
Doppelklick auf `start-local-server.bat` oder im Terminal:
```bash
start-local-server.bat
```

### Linux/Mac:
```bash
chmod +x start-local-server.sh
./start-local-server.sh
```

### Manuell:
```bash
cd public
php -S localhost:8000
```

## Dann öffnen Sie im Browser:
**http://localhost:8000**

## Alternative Ports

Falls Port 8000 belegt ist, können Sie einen anderen Port verwenden:

```bash
cd public
php -S localhost:8080
```

Oder:
```bash
cd public
php -S 127.0.0.1:8000
```

## Wichtige Hinweise

1. **Document Root**: Der Server läuft im `public/` Verzeichnis, da dort der `index.php` Einstiegspunkt ist.

2. **Datenbank**: Stellen Sie sicher, dass Ihre `.env` Datei konfiguriert ist und die Datenbank erreichbar ist.

3. **.htaccess**: Der PHP Built-in Server unterstützt keine `.htaccess` Dateien. Das Routing funktioniert über `public/index.php`.

4. **Statische Dateien**: CSS, JS und Bilder im `public/` Ordner sollten automatisch korrekt geladen werden.

## Für Produktions-ähnliche Tests

Falls Sie Apache/Nginx Features testen möchten (z.B. `.htaccess`), verwenden Sie:
- **XAMPP** (Windows)
- **WAMP** (Windows)
- **MAMP** (Mac)
- **LAMP** (Linux)

Dann konfigurieren Sie den Virtual Host auf das `public/` Verzeichnis als Document Root.

