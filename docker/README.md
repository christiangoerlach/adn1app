# Docker-Konfiguration

Dieses Verzeichnis enthält die Docker-Konfigurationsdateien für eine Azure-ähnliche lokale Entwicklungsumgebung.

## Dateien

- **Dockerfile**: PHP 8.2-FPM Image mit allen notwendigen Extensions (SQL Server, GD, etc.)
- **nginx.conf**: nginx-Konfiguration, die Azure App Service nachbildet
- **.dockerignore**: Dateien, die beim Docker-Build ignoriert werden sollen

## Verwendung

Siehe [README-DOCKER.md](../README-DOCKER.md) für Anleitung zur Verwendung.

## Anpassungen

### nginx-Konfiguration ändern

Bearbeite `nginx.conf` und starte die Container neu:

```bash
docker-compose restart nginx
```

Oder teste die Konfiguration:

```bash
docker-compose exec nginx nginx -t
docker-compose exec nginx nginx -s reload
```

### PHP-Extensions hinzufügen

Bearbeite `Dockerfile` und baue das Image neu:

```bash
docker-compose build web
docker-compose up -d
```



