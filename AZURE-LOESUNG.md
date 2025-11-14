# Lösung für 404-Fehler auf Azure App Service (nginx)

## Das Problem
Azure App Service für PHP 8.x verwendet **nginx**, das keine `.htaccess`-Dateien unterstützt. Daher werden Routen wie `/bewertungm` direkt von nginx mit 404 abgelehnt, bevor PHP erreicht wird.

## Lösung

### Option 1: App Service Startup Command setzen (EINFACHSTE LÖSUNG)

1. Gehen Sie zu **Azure Portal**
2. Wählen Sie Ihren App Service: `adntest1`
3. Navigieren Sie zu: **Configuration → General settings**
4. Scrollen Sie zu **Startup Command**
5. Geben Sie ein:
   ```
   startup.sh
   ```
6. Klicken Sie auf **Save**
7. **WICHTIG:** Starten Sie die App neu: **Overview → Restart**

### Option 2: App-Einstellung hinzufügen

1. Azure Portal → App Service → **Configuration → Application settings**
2. Klicken Sie auf **+ New application setting**
3. Fügen Sie hinzu:
   - **Name:** `WEBSITE_USE_PLACEHOLDER`
   - **Value:** `0`
4. Klicken Sie auf **Save**
5. Starten Sie die App neu

### Option 3: Document Root prüfen

Stellen Sie sicher, dass der Document Root korrekt gesetzt ist:

1. Azure Portal → App Service → **Configuration → Path mappings**
2. Prüfen Sie:
   - **Virtual path:** `/`
   - **Physical path:** `site\wwwroot`
3. Falls anders, ändern Sie es entsprechend

### Option 4: Manuelle nginx-Konfiguration (Falls Optionen 1-3 nicht funktionieren)

1. Verbinden Sie sich per SSH:
   - Azure Portal → App Service → **Development Tools → SSH**
   - Oder: `https://<AppName>.scm.azurewebsites.net/webssh/host`

2. Prüfen Sie die aktuelle nginx-Konfiguration:
   ```bash
   cat /etc/nginx/sites-available/default
   ```

3. Bearbeiten Sie die Konfiguration:
   ```bash
   sudo nano /etc/nginx/sites-available/default
   ```

4. Suchen Sie den `location /` Block und stellen Sie sicher, dass er so aussieht:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

5. Falls nicht vorhanden, fügen Sie es hinzu und speichern Sie (Ctrl+O, Enter, Ctrl+X)

6. Testen Sie die Konfiguration:
   ```bash
   sudo nginx -t
   ```

7. Nginx neu laden:
   ```bash
   sudo nginx -s reload
   ```

## Verifizierung

Nach der Konfiguration testen Sie:

1. `https://adntest1-hzcjbsgrdwczetba.canadacentral-01.azurewebsites.net/` - Startseite sollte laden
2. `https://adntest1-hzcjbsgrdwczetba.canadacentral-01.azurewebsites.net/bewertungm` - Projektauswahl sollte laden

## Falls immer noch 404

1. **Logs prüfen:**
   - Azure Portal → App Service → **Log stream**
   - Prüfen Sie PHP- und nginx-Logs

2. **Test-Datei prüfen:**
   - `https://adntest1-hzcjbsgrdwczetba.canadacentral-01.azurewebsites.net/test-route.php`
   - Falls das funktioniert, ist PHP OK, aber Routing funktioniert nicht

3. **Startup-Script prüfen:**
   - Per SSH: `cat /home/site/wwwroot/startup.sh`
   - Prüfen Sie ob es ausführbar ist: `ls -la /home/site/wwwroot/startup.sh`

## Wichtiger Hinweis

Die `startup.sh`-Datei wurde bereits erstellt und sollte automatisch nginx konfigurieren. Falls Option 1 nicht funktioniert, muss das Script möglicherweise manuell ausführbar gemacht werden:

```bash
chmod +x /home/site/wwwroot/startup.sh
```

