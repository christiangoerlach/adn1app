# Azure App Service Linux - nginx Konfiguration

## Problem
Azure App Service für PHP 8.x verwendet standardmäßig **nginx** statt Apache. Nginx unterstützt keine `.htaccess`-Dateien direkt.

## Lösung

### Option 1: Startup-Script (Empfohlen)
Die Datei `startup.sh` wurde erstellt und sollte automatisch ausgeführt werden. Falls nicht, müssen Sie in den Azure App Service-Einstellungen den Startbefehl konfigurieren:

**Azure Portal → App Service → Configuration → General settings → Startup Command:**
```bash
/home/site/wwwroot/startup.sh
```

### Option 2: App Service-Einstellung
Alternativ können Sie eine App-Einstellung hinzufügen:

**Azure Portal → App Service → Configuration → Application settings:**

**Name:** `WEBSITE_ENABLE_SYNC_UPDATE_SITE`
**Wert:** `true`

**Name:** `SCM_DO_BUILD_DURING_DEPLOYMENT`
**Wert:** `true`

### Option 3: Document Root ändern (Falls nötig)
Falls der Document Root auf `/home/site/wwwroot/public` gesetzt werden soll:

**Azure Portal → App Service → Configuration → Path mappings:**

**Virtual path:** `/`
**Physical path:** `site\wwwroot\public`

## Verifizierung
Nach dem Deployment testen Sie:
1. `https://<IhrAppName>.azurewebsites.net/` - Sollte die Startseite zeigen
2. `https://<IhrAppName>.azurewebsites.net/bewertungm` - Sollte die Projektauswahl zeigen

## Debugging
Falls es immer noch nicht funktioniert:
1. Prüfen Sie die Logs: Azure Portal → App Service → Log stream
2. Prüfen Sie die nginx-Konfiguration über SSH/Kudu Console
3. Testen Sie `/test-route.php` um zu sehen, ob PHP läuft

