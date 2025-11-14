#!/bin/bash

# Startup-Script für Azure App Service Linux
# Minimale Version - führt nur das Standard-Startup-Script aus
# Azure sollte automatisch .htaccess-Dateien verarbeiten

echo "Starting Azure App Service..."

# Führe das Standard-Startup-Script aus
# Azure sollte automatisch .htaccess-Dateien in nginx-Konfiguration konvertieren
exec /opt/startup/startup.sh

