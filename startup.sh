#!/bin/bash

# Startup-Script für Azure App Service Linux
# Dieses Script konfiguriert nginx für URL-Rewriting

echo "Starting Azure App Service with URL rewriting..."

# Azure sollte automatisch .htaccess-Dateien verarbeiten
# Falls das nicht funktioniert, wird hier eine Fallback-Lösung bereitgestellt

# Prüfe ob nginx läuft und ob wir Berechtigung haben
if [ -w /etc/nginx/sites-available/ ] && command -v nginx &> /dev/null; then
    echo "Configuring nginx..."
    
    # Erstelle eine benutzerdefinierte nginx-Konfiguration
    cat > /tmp/nginx-default.conf << 'EOF'
server {
    listen 8080;
    server_name _;
    root /home/site/wwwroot;
    index index.php index.html;

    # Alle Anfragen an index.php weiterleiten (außer existierende Dateien)
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM-Konfiguration
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Sicherheit: Verhindere Zugriff auf sensible Dateien
    location ~ /\.(env|git) {
        deny all;
        return 404;
    }

    location ~ ^/(config|php|vendor)/ {
        deny all;
        return 404;
    }

    # Statische Dateien
    location ~* \.(jpg|jpeg|gif|png|css|js|ico|xml|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
EOF
    
    # Kopiere die Konfiguration falls möglich
    cp /tmp/nginx-default.conf /etc/nginx/sites-available/default 2>/dev/null || true
    nginx -t && nginx -s reload 2>/dev/null || true
fi

# Führe das Standard-Startup-Script aus
exec /opt/startup/startup.sh

