/**
 * Admin Members Management JavaScript
 */

function editMembre(membre) {
    // Populate form fields
    document.getElementById('edit-id').value = membre.id_membre;
    document.getElementById('edit-nom').value = membre.nom;
    document.getElementById('edit-prenom').value = membre.prenom;
    document.getElementById('edit-email').value = membre.email;
    document.getElementById('edit-poste').value = membre.poste;
    document.getElementById('edit-grade').value = membre.grade;
    document.getElementById('edit-role').value = membre.role;
    document.getElementById('edit-role-systeme').value = membre.role_systeme;
    document.getElementById('edit-specialite').value = membre.specialite || '';
    document.getElementById('edit-domaine').value = membre.domaine_recherche || '';
    document.getElementById('edit-biographie').value = membre.biographie || '';
    document.getElementById('edit-actif').checked = membre.actif == 1;

    // Clear password field
    document.getElementById('edit-password').value = '';

    // Open modal
    openModal('modal-edit-membre');
}

// Auto-open add modal if there are errors
document.addEventListener('DOMContentLoaded', function () {
    // Check if we should open add modal (after failed submission)
    const urlParams = new URLSearchParams(window.location.search);
    const hasErrors = document.querySelector('.flash-message.flash-error');

    if (hasErrors && !urlParams.has('action')) {
        // If there's an error and no action param, it's probably a failed add
        openModal('modal-add-membre');
    }
});