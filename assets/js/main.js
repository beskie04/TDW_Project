// ==================== NAVIGATION MOBILE MENU ====================
(function() {
    'use strict';
    
    function initNavigation() {
        const toggle = document.querySelector('.mobile-toggle');
        const menu = document.querySelector('.nav-menu');
        
        if (!toggle || !menu) {
            console.log('Navigation: mobile-toggle or nav-menu not found');
            return;
        }
        
        console.log('Navigation: Initialized successfully');
        
        // Toggle menu on click
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('active');
            console.log('Menu toggled:', menu.classList.contains('active'));
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('active');
            }
        });
        
        // Close menu when clicking a link
        const menuLinks = menu.querySelectorAll('a');
        menuLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                menu.classList.remove('active');
            });
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavigation);
    } else {
        initNavigation();
    }
})();

// ==================== FLASH MESSAGES ====================
document.addEventListener('DOMContentLoaded', function () {
    const closeButtons = document.querySelectorAll('.close-flash');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            this.parentElement.style.display = 'none';
        });
    });

    // Auto-fermeture après 5 secondes
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                msg.style.display = 'none';
            }, 300);
        }, 5000);
    });
});

// Animation de sortie pour les messages flash
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ==================== FILTRAGE DES PROJETS ====================
if (document.getElementById('filter-thematique')) {
    const filterThematique = document.getElementById('filter-thematique');
    const filterStatut = document.getElementById('filter-statut');
    const filterResponsable = document.getElementById('filter-responsable');
    const resetBtn = document.getElementById('reset-filters');
    const projetsContainer = document.getElementById('projets-container');
    const loading = document.getElementById('loading');

    function filterProjets() {
        const thematique = filterThematique.value;
        const statut = filterStatut.value;
        const responsable = filterResponsable.value;

        loading.style.display = 'block';
        projetsContainer.style.opacity = '0.5';

        const params = new URLSearchParams({
            page: 'projets',
            action: 'filter',
            thematique: thematique,
            statut: statut,
            responsable: responsable
        });

        fetch('?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    projetsContainer.innerHTML = data.html;
                    projetsContainer.style.opacity = '1';
                    loading.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                loading.style.display = 'none';
                projetsContainer.style.opacity = '1';
            });
    }

    filterThematique.addEventListener('change', filterProjets);
    filterStatut.addEventListener('change', filterProjets);
    filterResponsable.addEventListener('change', filterProjets);

    resetBtn.addEventListener('click', function () {
        filterThematique.value = '';
        filterStatut.value = '';
        filterResponsable.value = '';
        filterProjets();
    });
}

// ==================== FILTRAGE DES PUBLICATIONS ====================
if (document.getElementById('filter-annee') && document.getElementById('publications-container')) {
    console.log('Publications filters: Initializing...');

    const filterAnnee = document.getElementById('filter-annee');
    const filterType = document.getElementById('filter-type');
    const filterDomaine = document.getElementById('filter-domaine');
    const filterAuteur = document.getElementById('filter-auteur');
    const searchInput = document.getElementById('search-input');
    const resetBtn = document.getElementById('reset-filters');
    const publicationsContainer = document.getElementById('publications-container');
    const loading = document.getElementById('loading');

    let searchTimeout;

    if (filterAnnee && filterType && filterDomaine && filterAuteur && searchInput && resetBtn) {
        console.log('Publications filters: All elements found');

        function filterPublications() {
            console.log('Publications filters: Filtering...');

            const annee = filterAnnee.value;
            const type = filterType.value;
            const domaine = filterDomaine.value;
            const auteur = filterAuteur.value;
            const search = searchInput.value;

            console.log('Filter values:', { annee, type, domaine, auteur, search });

            if (loading) {
                loading.style.display = 'block';
            }
            publicationsContainer.style.opacity = '0.5';

            const params = new URLSearchParams({
                page: 'publications',
                action: 'filter',
                annee: annee,
                type: type,
                domaine: domaine,
                auteur: auteur,
                search: search
            });

            const url = '?' + params.toString();
            console.log('Fetching:', url);

            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Raw response:', text.substring(0, 200));

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                    }

                    console.log('Parsed data:', data);

                    if (data.success) {
                        publicationsContainer.innerHTML = data.html;
                        publicationsContainer.style.opacity = '1';
                        if (loading) {
                            loading.style.display = 'none';
                        }
                        console.log('Publications filters: Success! Found', data.count, 'publications');
                    } else {
                        console.error('Publications filters: Server returned success=false');
                        publicationsContainer.innerHTML = data.html || `
                            <div style="padding: 2rem; text-align: center; color: #ef4444;">
                                <h3>Erreur de filtrage</h3>
                                <p>${data.error || 'Erreur inconnue'}</p>
                            </div>
                        `;
                        publicationsContainer.style.opacity = '1';
                        if (loading) {
                            loading.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    console.error('Publications filters: Fetch Error:', error);
                    publicationsContainer.innerHTML = `
                        <div style="padding: 2rem; text-align: center; color: #ef4444;">
                            <h3>Erreur de connexion</h3>
                            <p>${error.message}</p>
                        </div>
                    `;
                    if (loading) {
                        loading.style.display = 'none';
                    }
                    publicationsContainer.style.opacity = '1';
                });
        }

        filterAnnee.addEventListener('change', filterPublications);
        filterType.addEventListener('change', filterPublications);
        filterDomaine.addEventListener('change', filterPublications);
        filterAuteur.addEventListener('change', filterPublications);

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterPublications, 500);
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                filterAnnee.value = '';
                filterType.value = '';
                filterDomaine.value = '';
                filterAuteur.value = '';
                searchInput.value = '';
                filterPublications();
            });
        }

        console.log('Publications filters: Setup complete');
    }
}

// ==================== FILTRAGE DES ÉQUIPEMENTS ====================
if (document.getElementById('filter-type') && document.querySelector('.equipements-grid')) {
    const filterType = document.getElementById('filter-type');
    const filterEtat = document.getElementById('filter-etat');
    const searchInput = document.getElementById('search-input');
    const resetBtn = document.getElementById('reset-filters');
    const equipementsContainer = document.getElementById('equipements-container');
    const loading = document.getElementById('loading');

    let searchTimeout;

    function filterEquipements() {
        const type = filterType.value;
        const etat = filterEtat.value;
        const search = searchInput.value;

        loading.style.display = 'block';
        equipementsContainer.style.opacity = '0.5';

        const params = new URLSearchParams({
            page: 'equipements',
            action: 'filter',
            type: type,
            etat: etat,
            search: search
        });

        fetch('?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    equipementsContainer.innerHTML = data.html;
                    equipementsContainer.style.opacity = '1';
                    loading.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                loading.style.display = 'none';
                equipementsContainer.style.opacity = '1';
            });
    }

    filterType.addEventListener('change', filterEquipements);
    filterEtat.addEventListener('change', filterEquipements);

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterEquipements, 500);
    });

    resetBtn.addEventListener('click', function () {
        filterType.value = '';
        filterEtat.value = '';
        searchInput.value = '';
        filterEquipements();
    });
}
