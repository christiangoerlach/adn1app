/**
 * JavaScript für die Projektseite
 * Verwaltet die Projektauswahl und AJAX-Requests
 */

document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('projekt-auswahl');
    
    if (projectSelect) {
        projectSelect.addEventListener('change', function() {
            const projectId = this.value;
            
            if (projectId) {
                selectProject(projectId);
            }
        });
    }
});

/**
 * Wählt ein Projekt aus und lädt die Projektübersicht
 * @param {string} projectId Die Projekt-ID
 */
function selectProject(projectId) {
    const formData = new FormData();
    formData.append('auswahl', projectId);

    // Projekt auswählen
    fetch('/', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Nach Setzen der Session den Projektbereich neu laden
            loadProjectOverview();
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
    fetch('/api/project-overview')
        .then(response => response.text())
        .then(html => {
            document.getElementById('projekt-content').innerHTML = html;
        })
        .catch(error => {
            console.error('Fehler beim Laden der Projektübersicht:', error);
            document.getElementById('projekt-content').innerHTML = 
                '<p>Fehler beim Laden der Projektübersicht</p>';
        });
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

