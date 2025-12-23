<?php
require_once __DIR__ . '/BaseView.php';

class EquipementView extends BaseView
{

    public function __construct()
    {
        $this->currentPage = 'equipements';
        $this->pageTitle = 'Équipements et Ressources';
    }

    /**
     * Liste des équipements
     */
    public function renderListe($equipements, $types, $etats, $mesReservations = [])
    {
        $this->renderHeader();
        $this->renderFlashMessage();

        $isLoggedIn = isset($_SESSION['user']);
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="page-header">
                    <h1><i class="fas fa-tools"></i> Gestion des Équipements et Ressources</h1>
                    <p class="subtitle">Consultez et réservez les équipements du laboratoire</p>
                </div>

                <?php if ($isLoggedIn && !empty($mesReservations)): ?>
                    <!-- Mes réservations -->
                    <div class="mes-reservations">
                        <h2><i class="fas fa-calendar-check"></i> Mes réservations en cours</h2>
                        <div class="reservations-grid">
                            <?php foreach ($mesReservations as $res): ?>
                                <div class="reservation-card">
                                    <h4><?= htmlspecialchars($res['equipement_nom']) ?></h4>
                                    <p class="reservation-dates">
                                        <i class="fas fa-calendar"></i>
                                        <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?> -
                                        <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?>
                                    </p>
                                    <button onclick="annulerReservation(<?= $res['id'] ?>)" class="btn-cancel-small">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Filtres -->
                <div class="filters-section">
                    <div class="filters-wrapper">
                        <div class="filter-group">
                            <label><i class="fas fa-tag"></i> Type</label>
                            <select id="filter-type" class="filter-select">
                                <option value="">Tous les types</option>
                                <?php foreach ($types as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-circle"></i> État</label>
                            <select id="filter-etat" class="filter-select">
                                <option value="">Tous les états</option>
                                <?php foreach ($etats as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group search-group">
                            <label><i class="fas fa-search"></i> Rechercher</label>
                            <input type="text" id="search-input" placeholder="Nom, description..." class="search-input">
                        </div>

                        <button id="reset-filters" class="btn-reset">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </button>
                    </div>
                </div>

                <!-- Liste des équipements -->
                <div id="equipements-container" class="equipements-grid">
                    <?php $this->renderEquipementsList($equipements, $isLoggedIn); ?>
                </div>

                <div id="loading" class="loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Chargement...
                </div>
            </div>
        </main>

        <script>
            function annulerReservation(id) {
                if (confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
                    window.location.href = '?page=equipements&action=annuler&id=' + id;
                }
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Liste des équipements (pour AJAX)
     */
    public function renderEquipementsList($equipements, $isLoggedIn = false)
    {
        if (empty($equipements)) {
            echo '<div class="no-results"><i class="fas fa-inbox"></i><p>Aucun équipement trouvé</p></div>';
            return;
        }

        foreach ($equipements as $eq) {
            $this->renderEquipementCard($eq, $isLoggedIn);
        }
    }

    /**
     * Carte d'équipement
     */
    private function renderEquipementCard($eq, $isLoggedIn)
    {
        $etatClass = $this->getEtatClass($eq['etat']);
        $etatLabel = ETATS_EQUIPEMENTS[$eq['etat']] ?? $eq['etat'];
        $typeLabel = TYPES_EQUIPEMENTS[$eq['type']] ?? $eq['type'];
        ?>
        <div class="equipement-card">
            <div class="equipement-header">
                <span class="equipement-type"><?= htmlspecialchars($typeLabel) ?></span>
                <span class="equipement-etat <?= $etatClass ?>">
                    <?= htmlspecialchars($etatLabel) ?>
                </span>
            </div>

            <h3 class="equipement-nom"><?= htmlspecialchars($eq['nom']) ?></h3>

            <p class="equipement-description">
                <?= htmlspecialchars($eq['description'] ?? '') ?>
            </p>

            <?php if (!empty($eq['specifications'])): ?>
                <p class="equipement-specs">
                    <i class="fas fa-info-circle"></i>
                    <?= htmlspecialchars($eq['specifications']) ?>
                </p>
            <?php endif; ?>

            <div class="equipement-footer">
                <?php if ($isLoggedIn && $eq['etat'] === 'libre'): ?>
                    <a href="?page=equipements&action=reserver&id=<?= $eq['id'] ?>" class="btn-primary">
                        <i class="fas fa-calendar-plus"></i> Réserver
                    </a>
                <?php else: ?>
                    <button class="btn-secondary" disabled>
                        <i class="fas fa-ban"></i> Non disponible
                    </button>
                <?php endif; ?>

                <a href="?page=equipements&action=details&id=<?= $eq['id'] ?>" class="btn-details-link">
                    Voir détails <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Formulaire de réservation
     */
    public function renderReservation($equipement)
    {
        $this->pageTitle = 'Réserver - ' . $equipement['nom'];
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="breadcrumb">
                    <a href="?page=accueil">Accueil</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=equipements">Équipements</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Réserver</span>
                </div>

                <div class="reservation-form-container">
                    <h1><i class="fas fa-calendar-plus"></i> Réserver un équipement</h1>

                    <div class="equipement-info-box">
                        <h3><?= htmlspecialchars($equipement['nom']) ?></h3>
                        <p><?= htmlspecialchars($equipement['description'] ?? '') ?></p>
                    </div>

                    <form method="POST" action="?page=equipements&action=confirmer_reservation" class="reservation-form">
                        <input type="hidden" name="id_equipement" value="<?= $equipement['id'] ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="date_debut">Date et heure de début *</label>
                                <input type="datetime-local" id="date_debut" name="date_debut" min="<?= date('Y-m-d\TH:i') ?>"
                                    required class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="date_fin">Date et heure de fin *</label>
                                <input type="datetime-local" id="date_fin" name="date_fin" min="<?= date('Y-m-d\TH:i') ?>"
                                    required class="form-control">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-check"></i> Confirmer la réservation
                            </button>
                            <a href="?page=equipements" class="btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <script>
            // Valider que la date de fin est après la date de début
            document.getElementById('date_fin').addEventListener('change', function () {
                const debut = document.getElementById('date_debut').value;
                const fin = this.value;

                if (debut && fin && fin <= debut) {
                    alert('La date de fin doit être après la date de début');
                    this.value = '';
                }
            });
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Page détails d'un équipement
     */
    public function renderDetails($equipement, $reservations)
    {
        $this->pageTitle = $equipement['nom'];
        $this->renderHeader();
        $this->renderFlashMessage();

        $etatClass = $this->getEtatClass($equipement['etat']);
        $etatLabel = ETATS_EQUIPEMENTS[$equipement['etat']] ?? $equipement['etat'];
        $isLoggedIn = isset($_SESSION['user']);
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="breadcrumb">
                    <a href="?page=accueil">Accueil</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=equipements">Équipements</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= htmlspecialchars($equipement['nom']) ?></span>
                </div>

                <div class="equipement-details">
                    <div class="details-header">
                        <div class="header-top">
                            <span
                                class="equipement-type large"><?= htmlspecialchars(TYPES_EQUIPEMENTS[$equipement['type']] ?? $equipement['type']) ?></span>
                            <span class="equipement-etat <?= $etatClass ?> large">
                                <?= htmlspecialchars($etatLabel) ?>
                            </span>
                        </div>
                        <h1><?= htmlspecialchars($equipement['nom']) ?></h1>
                    </div>

                    <div class="details-grid">
                        <div class="details-main">
                            <section class="detail-section">
                                <h2><i class="fas fa-align-left"></i> Description</h2>
                                <p><?= nl2br(htmlspecialchars($equipement['description'] ?? '')) ?></p>
                            </section>

                            <?php if (!empty($equipement['specifications'])): ?>
                                <section class="detail-section">
                                    <h2><i class="fas fa-info-circle"></i> Spécifications</h2>
                                    <p><?= nl2br(htmlspecialchars($equipement['specifications'])) ?></p>
                                </section>
                            <?php endif; ?>

                            <?php if (!empty($reservations)): ?>
                                <section class="detail-section">
                                    <h2><i class="fas fa-calendar"></i> Réservations en cours</h2>
                                    <div class="reservations-list">
                                        <?php foreach ($reservations as $res): ?>
                                            <div class="reservation-item">
                                                <div class="reservation-membre">
                                                    <i class="fas fa-user"></i>
                                                    <?= htmlspecialchars($res['membre_nom'] . ' ' . $res['membre_prenom']) ?>
                                                </div>
                                                <div class="reservation-dates">
                                                    <i class="fas fa-calendar"></i>
                                                    <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?> -
                                                    <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>
                        </div>

                        <div class="details-sidebar">
                            <div class="info-card">
                                <h3><i class="fas fa-info-circle"></i> Actions</h3>
                                <?php if ($isLoggedIn && $equipement['etat'] === 'libre'): ?>
                                    <a href="?page=equipements&action=reserver&id=<?= $equipement['id'] ?>"
                                        class="btn-primary btn-block">
                                        <i class="fas fa-calendar-plus"></i> Réserver
                                    </a>
                                <?php else: ?>
                                    <button class="btn-secondary btn-block" disabled>
                                        <i class="fas fa-ban"></i> Non disponible
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Classe CSS selon l'état
     */
    private function getEtatClass($etat)
    {
        switch ($etat) {
            case 'libre':
                return 'etat-libre';
            case 'reserve':
                return 'etat-reserve';
            case 'en_maintenance':
                return 'etat-maintenance';
            default:
                return 'etat-default';
        }
    }
}
?>