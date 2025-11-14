/**
 * JavaScript für die Projektseite
 * Verwaltet die Projektauswahl und AJAX-Requests
 */

// Initialisierung beim Laden der Seite
function initProjectSelect() {
    const projectSelect = document.getElementById('projekt-auswahl');
    
    if (projectSelect && !projectSelect.hasAttribute('data-initialized')) {
        projectSelect.setAttribute('data-initialized', 'true');
        projectSelect.addEventListener('change', function() {
            const projectId = this.value;
            
            if (projectId) {
                selectProject(projectId);
            }
        });
    }
}

// Bei DOMContentLoaded ausführen
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProjectSelect);
} else {
    // DOM ist bereits geladen
    initProjectSelect();
}

/**
 * Wählt ein Projekt aus und lädt die Projektübersicht
 * @param {string} projectId Die Projekt-ID
 */
function selectProject(projectId) {
    const formData = new FormData();
    formData.append('auswahl', projectId);
    
    // UI-Status speichern
    const select = document.getElementById('projekt-auswahl');
    const originalValue = select.value;
    const content = document.getElementById('projekt-content');
    const originalContent = content.innerHTML;
    
    // Ladeindikator anzeigen
    select.disabled = true;
    content.innerHTML = '<div style="padding: 20px; text-align: center;"><p>Projekt wird geladen...</p><div style="border: 4px solid #f3f3f3; border-top: 4px solid #007bff; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto;"></div></div>';
    
    // CSS für Spinner-Animation hinzufügen, falls noch nicht vorhanden
    if (!document.getElementById('spinner-style')) {
        const style = document.createElement('style');
        style.id = 'spinner-style';
        style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }

    // Projekt auswählen (an /index.php?path=bewertungm senden)
    fetch('/index.php?path=bewertungm', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Seite neu laden, um Statistiken zu aktualisieren
            window.location.reload();
        } else {
            // Fehler: Original-Zustand wiederherstellen
            select.disabled = false;
            select.value = originalValue;
            content.innerHTML = originalContent;
            console.error('Fehler beim Auswählen des Projekts:', data.error);
            alert('Fehler beim Auswählen des Projekts: ' + (data.error || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        // Fehler: Original-Zustand wiederherstellen
        select.disabled = false;
        select.value = originalValue;
        content.innerHTML = originalContent;
        console.error('Fehler:', error);
        alert('Fehler beim Auswählen des Projekts. Bitte versuchen Sie es erneut.');
    });
}

/**
 * Lädt die Projektübersicht neu
 */
function loadProjectOverview() {
    // Wenn index_projekt.php eingebunden ist, einfach die Seite neu laden
    // da die Statistiken dann automatisch aktualisiert werden
    window.location.reload();
}

/**
 * Zeigt eine Erfolgsmeldung an
 * @param {string} message Die anzuzeigende Nachricht
 */
function showSuccess(message) {
    // Einfache Erfolgsmeldung implementieren
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.textContent = message;
    successDiv.style.cssText = `
        background-color: #d4edda;
        color: #155724;
        padding: 10px;
        border-radius: 4px;
        margin: 10px 0;
        border: 1px solid #c3e6cb;
    `;
    
    const content = document.getElementById('projekt-content');
    content.insertBefore(successDiv, content.firstChild);
    
    // Nach 3 Sekunden ausblenden
    setTimeout(() => {
        successDiv.remove();
    }, 3000);
}

/**
 * Zeigt eine Fehlermeldung an
 * @param {string} message Die anzuzeigende Fehlermeldung
 */
function showError(message) {
    // Einfache Fehlermeldung implementieren
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 4px;
        margin: 10px 0;
        border: 1px solid #f5c6cb;
    `;
    
    const content = document.getElementById('projekt-content');
    content.insertBefore(errorDiv, content.firstChild);
    
    // Nach 5 Sekunden ausblenden
    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
}


