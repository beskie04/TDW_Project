// Fermeture des messages flash
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

// Filtrage des projets
if (document.getElementById('filter-thematique')) {
    const filterThematique = document.getElementById('filter-thematique');
    const filterStatut = document.getElementById('filter-statut');
    const filterResponsable = document.getElementById('filter-responsable');
    const resetBtn = document.getElementById('reset-filters');
    const projetsContainer = document.getElementById('projets-container');
    const loading = document.getElementById('loading');

    // Fonction de filtrage
    function filterProjets() {
        const thematique = filterThematique.value;
        const statut = filterStatut.value;
        const responsable = filterResponsable.value;

        // Afficher le loading
        loading.style.display = 'block';
        projetsContainer.style.opacity = '0.5';

        // Construire l'URL
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

    // Événements sur les filtres
    filterThematique.addEventListener('change', filterProjets);
    filterStatut.addEventListener('change', filterProjets);
    filterResponsable.addEventListener('change', filterProjets);

    // Réinitialiser les filtres
    resetBtn.addEventListener('click', function () {
        filterThematique.value = '';
        filterStatut.value = '';
        filterResponsable.value = '';
        filterProjets();
    });
}

// Filtrage des publications (AJAX) - COMPLETELY ISOLATED
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

    // Check if all elements exist
    if (filterAnnee && filterType && filterDomaine && filterAuteur && searchInput && resetBtn) {
        console.log('Publications filters: All elements found');

        console.log('Publications filters: All elements found');

        // Fonction de filtrage
        function filterPublications() {
            console.log('Publications filters: Filtering...');

            const annee = filterAnnee.value;
            const type = filterType.value;
            const domaine = filterDomaine.value;
            const auteur = filterAuteur.value;
            const search = searchInput.value;

            console.log('Filter values:', { annee, type, domaine, auteur, search });

            // Afficher le loading
            if (loading) {
                loading.style.display = 'block';
            }
            publicationsContainer.style.opacity = '0.5';

            // Construire l'URL
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

            // Requête AJAX
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        publicationsContainer.innerHTML = data.html;
                        publicationsContainer.style.opacity = '1';
                        if (loading) {
                            loading.style.display = 'none';
                        }
                        console.log('Publications filters: Success! Found', data.count, 'publications');
                    } else {
                        console.error('Publications filters: Server returned success=false');
                    }
                })
                .catch(error => {
                    console.error('Publications filters: Error:', error);
                    if (loading) {
                        loading.style.display = 'none';
                    }
                    publicationsContainer.style.opacity = '1';
                });
        }

        // Événements sur les filtres
        filterAnnee.addEventListener('change', function () {
            console.log('Année changed:', this.value);
            filterPublications();
        });

        filterType.addEventListener('change', function () {
            console.log('Type changed:', this.value);
            filterPublications();
        });

        filterDomaine.addEventListener('change', function () {
            console.log('Domaine changed:', this.value);
            filterPublications();
        });

        filterAuteur.addEventListener('change', function () {
            console.log('Auteur changed:', this.value);
            filterPublications();
        });

        // Recherche avec délai
        searchInput.addEventListener('input', function () {
            console.log('Search input:', this.value);
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterPublications, 500);
        });

        // Réinitialiser les filtres
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                console.log('Reset button clicked');
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

    // Filtrage des équipements (AJAX)
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
        filterEtat.addEventListener('change', filterEtat);

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

    // Menu mobile toggle
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function () {
            navMenu.classList.toggle('active');
        });
    }
}