# Azure CLI Befehle für App Service Konfiguration

## Startup-File setzen

```bash
az webapp config set --resource-group ressourcengruppe1 --name adntest1 --startup-file startup.sh
```

## Alternative: Mit explizitem Linux-FX-Version

```bash
az webapp config set --resource-group ressourcengruppe1 --name adntest1 --linux-fx-version "PHP|8.4" --startup-file startup.sh
```

## Aktuelle Konfiguration prüfen

```bash
az webapp config show --resource-group ressourcengruppe1 --name adntest1 --query linuxFxVersion
az webapp config show --resource-group ressourcengruppe1 --name adntest1 --query appCommandLine
```

## App neu starten (nach Konfiguration)

```bash
az webapp restart --resource-group ressourcengruppe1 --name adntest1
```

## Kompletter Workflow

```bash
# 1. Startup-File setzen
az webapp config set --resource-group ressourcengruppe1 --name adntest1 --startup-file startup.sh

# 2. App neu starten
az webapp restart --resource-group ressourcengruppe1 --name adntest1

# 3. Status prüfen
az webapp config show --resource-group ressourcengruppe1 --name adntest1 --query "{linuxFxVersion:linuxFxVersion,appCommandLine:appCommandLine}"
```

