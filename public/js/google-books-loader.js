// Initialiser quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    console.log('Préparation du bouton Google Books...');
    setTimeout(setupGoogleBooksButton, 500);
});

let lastGoogleBooksQueryAt = 0;
const GOOGLE_BOOKS_MIN_INTERVAL_MS = 2500;

// Detection pour les cas où le formulaire se racharge dynamiquement
if (window.MutationObserver && document.body) {
    let setupTimeout;
    const observer = new MutationObserver(function(mutations) {
        clearTimeout(setupTimeout);
        setupTimeout = setTimeout(() => {
            const exists = document.querySelector('.google-books-button');
            const buttonContainer = findButtonContainer();
            if (!exists && buttonContainer) {
                console.log('Réinitialisation du bouton après mutation du DOM');
                setupGoogleBooksButton();
            }
        }, 300);
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['id', 'class', 'style']
    });
}

function findButtonContainer() {
    // Cibler le formulaire EasyAdmin pour y insérer le bouton
    const formContainer = document.querySelector('.ea-new form, .ea-edit form, form');
    if (formContainer) {
        console.log('Formulaire trouvé');
        return formContainer;
    }

    console.log('Emplacement du bouton non trouvé');
    return null;
}

function setupGoogleBooksButton() {
    const buttonContainer = findButtonContainer();
    
    if (!buttonContainer) {
        console.log('Formulaire pas encore disponible, nouvelle tentative...');
        return;
    }

    // Vérifier si le bouton existe déjà
    if (buttonContainer.dataset.googleBooksSetup) {
        console.log('Bouton Google Books deja initialise');
        return;
    }
    buttonContainer.dataset.googleBooksSetup = 'true';

    // Créer le wrapper pour le bouton
    const wrapper = document.createElement('div');
    wrapper.className = 'google-books-button-wrapper';
    wrapper.style.marginBottom = '12px';

    // Créer le bouton
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-info google-books-button';
    button.textContent = 'Synchroniser avec Google Books';
    button.title = 'Charger les informations du livre depuis Google Books';
    
    wrapper.appendChild(button);

    // Insérer le bouton juste avant le champ titre si possible
    const titleField = document.querySelector('input[id*="_titre"], input[name*="[titre]"], input[id*="titre"]');
    const titleWrapper = titleField ? titleField.closest('.form-group, .field-text, .mb-3, .row') : null;

    if (titleWrapper && titleWrapper.parentElement) {
        titleWrapper.parentElement.insertBefore(wrapper, titleWrapper);
        console.log('Bouton Google Books ajouté juste avant le titre');
    } else {
        buttonContainer.prepend(wrapper);
        console.log('Bouton Google Books ajouté au formulaire (fallback)');
    }

    // Événement au clic du bouton
    button.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        openGoogleBooksPopup();
    });
}

function openGoogleBooksPopup() {
    // Vérifier si un modal existe déjà
    const existingModal = document.getElementById('googleBooksModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Créer le modal HTML
    const modal = document.createElement('div');
    modal.id = 'googleBooksModal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 99999;
        backdrop-filter: blur(2px);
        animation: fadeIn 0.2s ease;
    `;
    
    // Créer le contenu du modal
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
        background-color: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        width: 500px;
        max-width: 90%;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        animation: slideUp 0.3s ease;
    `;
    
    // Ajouter les animations CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    modalContent.innerHTML = `
        <h2 style="margin: 0 0 20px 0; color: #333; font-size: 20px;">Charger depuis Google Books</h2>
        <div>
            <label for="googleBooksIsbnInput" style="display: block; margin-bottom: 10px; font-weight: 600; color: #555; font-size: 14px;">
                Entrez l'ISBN du livre:
            </label>
            <input 
                type="text" 
                id="googleBooksIsbnInput" 
                placeholder="Ex: 207036822X"
                autocomplete="off"
                style="
                    width: 100%;
                    padding: 12px;
                    border: 2px solid #e0e0e0;
                    border-radius: 4px;
                    box-sizing: border-box;
                    margin-bottom: 15px;
                    font-size: 14px;
                    transition: border-color 0.2s;
                "
                onmouseover="this.style.borderColor='#0066cc'"
                onmouseout="this.style.borderColor='#e0e0e0'"
                onfocus="this.style.borderColor='#0066cc'"
                onblur="this.style.borderColor='#e0e0e0'"
            />
            <div id="loadingMessage" style="display: none; color: #0066cc; margin-bottom: 15px; font-weight: 500; font-size: 14px;">
                Chargement en cours...
            </div>
            <div id="errorMessage" style="display: none; color: #d32f2f; margin-bottom: 15px; padding: 12px; background-color: #ffebee; border-radius: 4px; border-left: 4px solid #d32f2f; font-size: 14px;"></div>
            <div id="successMessage" style="display: none; color: #388e3c; margin-bottom: 15px; padding: 12px; background-color: #f1f8e9; border-radius: 4px; border-left: 4px solid #388e3c; font-size: 14px;">
                Données chargées avec succès!
            </div>
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <button id="cancelButton" type="button" style="
                padding: 10px 20px;
                cursor: pointer;
                border: 1px solid #ccc;
                background: #f5f5f5;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 500;
                color: #333;
                transition: all 0.2s;
            " onmouseover="this.style.backgroundColor='#e0e0e0'" onmouseout="this.style.backgroundColor='#f5f5f5'">
                Annuler
            </button>
            <button id="searchButton" type="button" style="
                padding: 10px 20px;
                cursor: pointer;
                border: 1px solid #0066cc;
                background: #0066cc;
                color: white;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.2s;
            " onmouseover="this.style.backgroundColor='#0052a3'" onmouseout="this.style.backgroundColor='#0066cc'">
                Rechercher
            </button>
        </div>
    `;
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Focus sur l'input
    const isbnInput = document.getElementById('googleBooksIsbnInput');
    setTimeout(() => isbnInput.focus(), 100);
    
    // Événement du bouton Annuler
    document.getElementById('cancelButton').addEventListener('click', function() {
        modal.remove();
    });
    
    // Événement du bouton Rechercher
    document.getElementById('searchButton').addEventListener('click', function() {
        searchAndLoadBook(modal);
    });
    
    // Permettre la recherche avec Entrée
    isbnInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchAndLoadBook(modal);
        }
    });
    
    // Fermer au clic en dehors
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

function searchAndLoadBook(modal) {
    const isbn = document.getElementById('googleBooksIsbnInput').value.trim();
    const loadingMessage = document.getElementById('loadingMessage');
    const errorMessage = document.getElementById('errorMessage');
    const successMessage = document.getElementById('successMessage');
    const searchButton = document.getElementById('searchButton');
    
    // Réinitialiser les messages
    errorMessage.style.display = 'none';
    errorMessage.innerText = '';
    successMessage.style.display = 'none';
    
    if (!isbn) {
        errorMessage.innerText = 'Veuillez entrer un ISBN';
        errorMessage.style.display = 'block';
        return;
    }

    const now = Date.now();
    if (now - lastGoogleBooksQueryAt < GOOGLE_BOOKS_MIN_INTERVAL_MS) {
        const waitSeconds = Math.ceil((GOOGLE_BOOKS_MIN_INTERVAL_MS - (now - lastGoogleBooksQueryAt)) / 1000);
        errorMessage.innerText = 'Merci d\'attendre ' + waitSeconds + 's avant une nouvelle recherche.';
        errorMessage.style.display = 'block';
        return;
    }
    lastGoogleBooksQueryAt = now;
    
    // Afficher le message de chargement
    loadingMessage.style.display = 'block';
    searchButton.disabled = true;
    searchButton.style.opacity = '0.6';
    searchButton.style.cursor = 'not-allowed';
    
    console.log('Recherche pour ISBN: ' + isbn);
    
    // Envoyer la requête au serveur
    fetch('/admin/google-books/search', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'isbn=' + encodeURIComponent(isbn)
    })
    .then(async response => {
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            const serverMessage = payload && payload.error ? payload.error : ('Erreur réseau: ' + response.status);
            throw new Error(serverMessage);
        }
        return payload;
    })
    .then(data => {
        loadingMessage.style.display = 'none';
        searchButton.disabled = false;
        searchButton.style.opacity = '1';
        searchButton.style.cursor = 'pointer';
        
        if (data.error) {
            console.error('Erreur API: ' + data.error);
            errorMessage.innerText = '' + data.error;
            errorMessage.style.display = 'block';
            return;
        }
        
        console.log('Données reçues de Google Books');
        
        // Charger les données dans le formulaire
        loadDataToForm(data);
        
        // Afficher le message de succès
        successMessage.style.display = 'block';
        
        // Fermer le modal après 2 secondes
        setTimeout(() => {
            modal.remove();
        }, 2000);
    })
    .catch(error => {
        loadingMessage.style.display = 'none';
        searchButton.disabled = false;
        searchButton.style.opacity = '1';
        searchButton.style.cursor = 'pointer';
        console.error('Erreur: ' + error.message);
        errorMessage.innerText = 'Erreur: ' + error.message;
        errorMessage.style.display = 'block';
    });
}

function loadDataToForm(data) {
    console.log('Chargement des données dans le formulaire...');
    
    // Charger le titre
    let titreFields = document.querySelectorAll('input[id*="titre"], input[name*="titre"]');
    let titreLoaded = false;
    
    for (let field of titreFields) {
        if (field.type !== 'hidden' && data.titre) {
            field.value = data.titre;
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
            console.log('Titre chargé: ' + data.titre);
            titreLoaded = true;
            break;
        }
    }
    
    if (!titreLoaded && data.titre) {
        console.warn('Champ titre non trouvé');
    }
    
    // Charger la description
    let descriptionFields = document.querySelectorAll('textarea[id*="description"], textarea[name*="description"]');
    let descriptionLoaded = false;
    
    for (let field of descriptionFields) {
        if (data.description) {
            field.value = data.description;
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
            console.log('Description chargée: ' + data.description.substring(0, 50) + '...');
            descriptionLoaded = true;
            break;
        }
    }
    
    if (!descriptionLoaded && data.description) {
        console.warn('Champ description non trouvé');
    }

    // Charger la date de sortie
    let dateFields = document.querySelectorAll('input[type="date"][id*="dateSortie"], input[type="date"][name*="dateSortie"], input[id*="dateSortie"], input[name*="dateSortie"]');
    let dateLoaded = false;

    for (let field of dateFields) {
        if (data.dateSortie) {
            field.value = data.dateSortie;
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
            console.log('Date de sortie chargée: ' + data.dateSortie);
            dateLoaded = true;
            break;
        }
    }

    if (!dateLoaded && data.dateSortie) {
        console.warn('Champ date de sortie non trouvé');
    }
}



