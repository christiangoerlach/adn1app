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

    // Projekt auswählen (an /bewertungm senden)
    fetch('/bewertungm', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Seite neu laden, um Statistiken zu aktualisieren
            window.location.reload();
        } else {
            console.error('Fehler beim Auswählen des Projekts:', data.error);
            alert('Fehler beim Auswählen des Projekts: ' + (data.error || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Fehler:', error);
        alert('Fehler beim Auswählen des Projekts');
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


