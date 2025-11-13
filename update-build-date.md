# Automatische APP_BUILD_DATE Aktualisierung

Es gibt zwei Methoden, um `APP_BUILD_DATE` automatisch zu aktualisieren:

## Option 1: Dynamische Berechnung (EMPFOHLEN) ✅

Die aktuelle Implementierung in `config/app.php` berechnet das Datum automatisch:
- **Git-basiert**: Verwendet das Datum des letzten Commits (wenn Git verfügbar ist)
- **Filemtime-Fallback**: Verwendet das Änderungsdatum von `config/app.php`

**Vorteile:**
- ✅ Keine manuelle Wartung nötig
- ✅ Immer aktuell
- ✅ Keine Git-Hooks erforderlich
- ✅ Funktioniert auch ohne Git

**Nachteile:**
- ⚠️ Git-Befehl wird bei jedem Seitenaufruf ausgeführt (kann leicht optimiert werden mit Caching)

## Option 2: Git Pre-Commit Hook

Falls Sie das Datum nur bei Commits aktualisieren möchten:

### Setup (einmalig):

**Für Windows (PowerShell):**
```powershell
# Git-Hook ausführbar machen (falls noch nicht geschehen)
# Die Datei .git/hooks/pre-commit.ps1 wurde bereits erstellt
```

**Für Unix/Linux/Mac:**
```bash
# Hook ausführbar machen
chmod +x .git/hooks/pre-commit
```

### Aktivierung:

Die Hooks sind bereits erstellt:
- `.git/hooks/pre-commit` (für Unix/Linux/Mac/Bash auf Windows)
- `.git/hooks/pre-commit.ps1` (für PowerShell auf Windows)

Git nutzt automatisch den passenden Hook je nach Umgebung.

**Vorteile:**
- ✅ Datum wird nur bei Commits aktualisiert
- ✅ Kein Git-Befehl bei Seitenaufrufen

**Nachteile:**
- ⚠️ Funktioniert nur, wenn Git verfügbar ist
- ⚠️ Hooks müssen einmalig aktiviert werden
- ⚠️ Muss manuell bei jedem Clone neu eingerichtet werden (Hooks werden nicht committed)

## Empfehlung

**Option 1 (dynamische Berechnung)** ist empfohlen, da sie:
- Sofort funktioniert
- Keine Konfiguration benötigt
- Immer aktuell ist

Falls die Performance ein Problem ist (Git-Befehl bei jedem Seitenaufruf), können Sie ein einfaches Caching hinzufügen oder zu Option 2 wechseln.

