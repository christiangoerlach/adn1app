# Azure App Service Konfiguration - Lösung für 404 Fehler

## Problem
Azure App Service für PHP 8.x verwendet **nginx** statt Apache. Nginx unterstützt keine `.htaccess`-Dateien direkt, daher gibt es 404-Fehler für Routen wie `/bewertungm`.

## Lösung

### Schritt 1: App-Einstellung in Azure konfigurieren

Gehen Sie zu **Azure Portal → App Service → Configuration → Application settings** und fügen Sie folgende Einstellung hinzu:

**Name:** `WEBSITE_WEBDEPLOY_USE_SCM`  
**Wert:** `true`

**ODER**

**Name:** `WEBSITE_USE_PLACEHOLDER`  
**Wert:** `0`

### Schritt 2: Startup-Script aktivieren (Falls Schritt 1 nicht funktioniert)

Gehen Sie zu **Azure Portal → App Service → Configuration → General settings**:

**Startup Command:**
```bash
/home/site/wwwroot/startup.sh
```

### Schritt 3: Alternative - Document Root ändern

Falls der Document Root auf `/home/site/wwwroot` statt `/home/site/wwwroot/public` gesetzt ist:

Gehen Sie zu **Azure Portal → App Service → Configuration → Path mappings** und ändern Sie:

**Virtual path:** `/`  
**Physical path:** `site\wwwroot`

(Stellen Sie sicher, dass `index.php` im Root-Verzeichnis liegt, nicht in `public/`)

### Schritt 4: Testen

Nach den Änderungen:
1. App neu starten: **Azure Portal → App Service → Overview → Restart**
2. Testen Sie:
   - `https://<AppName>.azurewebsites.net/`
   - `https://<AppName>.azurewebsites.net/bewertungm`

## Falls immer noch 404 Fehler

1. **Logs prüfen:**
   - Azure Portal → App Service → Log stream
   - Prüfen Sie nginx und PHP-Logs

2. **SSH verwenden:**
   - Azure Portal → App Service → SSH
   - Prüfen Sie die nginx-Konfiguration: `cat /etc/nginx/sites-available/default`
   - Prüfen Sie ob `startup.sh` ausgeführt wurde

3. **Test-Datei prüfen:**
   - Rufen Sie `https://<AppName>.azurewebsites.net/test-route.php` auf
   - Falls das funktioniert, ist PHP korrekt konfiguriert, aber das Routing funktioniert nicht

## Manuelle nginx-Konfiguration (Falls nötig)

Falls nichts funktioniert, können Sie die nginx-Konfiguration manuell anpassen über SSH:

```bash
# Verbinden Sie sich per SSH
# Azure Portal → App Service → SSH

# Prüfen Sie die aktuelle Konfiguration
cat /etc/nginx/sites-available/default

# Bearbeiten Sie die Konfiguration
nano /etc/nginx/sites-available/default

# Fügen Sie folgende Zeile hinzu (in der location / Block):
try_files $uri $uri/ /index.php?$query_string;

# Testen Sie die Konfiguration
nginx -t

# Nginx neu laden
nginx -s reload
```

## Weitere Hilfe

Falls das Problem weiterhin besteht, kontaktieren Sie den Azure-Support oder prüfen Sie die offizielle Dokumentation:
- https://learn.microsoft.com/azure/app-service/configure-common
- https://learn.microsoft.com/azure/app-service/configure-language-php

