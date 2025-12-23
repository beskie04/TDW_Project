<?php
require_once __DIR__ . '/BaseView.php';

class ProjetView extends BaseView
{

    public function __construct()
    {
        $this->currentPage = 'projets';
        $this->pageTitle = 'Projets de Recherche';
    }

    /**
     * Afficher la liste des projets (côté client)
     */
    public function renderListe($projets, $thematiques, $statuts, $responsables)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="page-header">
                    <h1><i class="fas fa-project-diagram"></i> Catalogue des Projets de Recherche</h1>
                    <p class="subtitle">Découvrez les projets de recherche menés par notre laboratoire</p>
                </div>

                <!-- Filtres -->
                <div class="filters-section">
                    <div class="filters-wrapper">
                        <div class="filter-group">
                            <label><i class="fas fa-tag"></i> Thématique</label>
                            <select id="filter-thematique" class="filter-select">
                                <option value="">Toutes les thématiques</option>
                                <?php foreach ($thematiques as $them): ?>
                                    <option value="<?= $them['id_thematique'] ?>"><?= htmlspecialchars($them['nom_thematique']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-user-tie"></i> Responsable</label>
                            <select id="filter-responsable" class="filter-select">
                                <option value="">Tous les responsables</option>
                                <?php foreach ($responsables as $resp): ?>
                                    <option value="<?= $resp['id_membre'] ?>">
                                        <?= htmlspecialchars($resp['nom'] . ' ' . $resp['prenom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-tasks"></i> Statut</label>
                            <select id="filter-statut" class="filter-select">
                                <option value="">Tous les statuts</option>
                                <?php foreach ($statuts as $stat): ?>
                                    <option value="<?= $stat['id_statut'] ?>"><?= htmlspecialchars($stat['nom_statut']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button id="reset-filters" class="btn-reset">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </button>
                    </div>
                </div>

                <!-- Liste des projets -->
                <div id="projets-container" class="projets-grid">
                    <?php $this->renderProjetsCards($projets); ?>
                </div>

                <div id="loading" class="loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Chargement...
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Afficher les cartes de projets (groupées par thématique pour AJAX)
     */
    public function renderProjetsCards($projets)
    {
        if (empty($projets)) {
            echo '<div class="no-results"><i class="fas fa-inbox"></i><p>Aucun projet trouvé</p></div>';
            return;
        }

        // Grouper par thématique
        $projetsParThematique = [];
        foreach ($projets as $projet) {
            $themId = $projet['id_thematique'] ?? 0;
            $themNom = $projet['thematique_nom'] ?? 'Non classé';
            if (!isset($projetsParThematique[$themId])) {
                $projetsParThematique[$themId] = [
                    'nom' => $themNom,
                    'projets' => []
                ];
            }
            $projetsParThematique[$themId]['projets'][] = $projet;
        }

        // Afficher par thématique
        foreach ($projetsParThematique as $groupe) {
            ?>
            <div class="thematique-section">
                <div class="thematique-header">
                    <h2><i class="fas fa-folder"></i> <?= htmlspecialchars($groupe['nom']) ?></h2>
                    <span class="projet-count"><?= count($groupe['projets']) ?> projet(s)</span>
                </div>
                <div class="projets-grid">
                    <?php foreach ($groupe['projets'] as $projet): ?>
                        <?php $this->renderProjetCard($projet); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Afficher une carte de projet
     */
    private function renderProjetCard($projet)
    {
        $statusClass = $this->getStatusClass($projet['statut_nom'] ?? '');

        // Récupérer les membres du projet
        require_once __DIR__ . '/../models/ProjetModel.php';
        $projetModel = new ProjetModel();
        $membres = $projetModel->getMembres($projet['id_projet']);
        ?>
        <div class="projet-card">
            <div class="projet-header">
                <span class="projet-thematique"><?= htmlspecialchars($projet['thematique_nom'] ?? 'Non défini') ?></span>
                <span class="projet-status <?= $statusClass ?>">
                    <?= htmlspecialchars($projet['statut_nom'] ?? 'Non défini') ?>
                </span>
            </div>

            <h3 class="projet-titre">
                <a href="?page=projets&action=details&id=<?= $projet['id_projet'] ?>">
                    <?= htmlspecialchars($projet['titre']) ?>
                </a>
            </h3>

            <p class="projet-description">
                <?= htmlspecialchars(mb_substr($projet['description'] ?? '', 0, 150)) ?>...
            </p>

            <div class="projet-meta">
                <div class="meta-item">
                    <i class="fas fa-user-tie"></i>
                    <span><strong>Responsable:</strong>
                        <?= htmlspecialchars(($projet['responsable_nom'] ?? '') . ' ' . ($projet['responsable_prenom'] ?? '')) ?></span>
                </div>

                <?php if (!empty($membres)): ?>
                    <div class="meta-item">
                        <i class="fas fa-users"></i>
                        <span><strong>Membres associés:</strong>
                            <?php
                            $nomsMembres = array_slice(array_map(function ($m) {
                                return $m['nom'] . ' ' . $m['prenom'];
                            }, $membres), 0, 3);
                            echo htmlspecialchars(implode(', ', $nomsMembres));
                            if (count($membres) > 3) {
                                echo ' +' . (count($membres) - 3) . ' autre(s)';
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="meta-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <span><strong>Financement:</strong>
                        <?= htmlspecialchars($projet['type_financement_nom'] ?? 'Non défini') ?></span>
                </div>
            </div>

            <div class="projet-footer">
                <a href="?page=projets&action=details&id=<?= $projet['id_projet'] ?>" class="btn-details">
                    Voir les détails <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Afficher les détails d'un projet
     */
    public function renderDetails($projet, $membres, $publications)
    {
        $this->pageTitle = $projet['titre'] . ' - Projet de Recherche';
        $this->renderHeader();
        $this->renderFlashMessage();

        $statusClass = $this->getStatusClass($projet['statut_nom'] ?? '');
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="breadcrumb">
                    <a href="?page=accueil">Accueil</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=projets">Projets</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= htmlspecialchars($projet['titre']) ?></span>
                </div>

                <div class="projet-details">
                    <div class="details-header">
                        <div class="header-top">
                            <span
                                class="projet-thematique large"><?= htmlspecialchars($projet['thematique_nom'] ?? 'Non défini') ?></span>
                            <span class="projet-status <?= $statusClass ?> large">
                                <?= htmlspecialchars($projet['statut_nom'] ?? 'Non défini') ?>
                            </span>
                        </div>
                        <h1><?= htmlspecialchars($projet['titre']) ?></h1>
                        <p class="projet-dates">
                            <i class="fas fa-calendar"></i>
                            <?= date('d/m/Y', strtotime($projet['date_debut'])) ?>
                            <?php if ($projet['date_fin']): ?>
                                - <?= date('d/m/Y', strtotime($projet['date_fin'])) ?>
                            <?php else: ?>
                                - En cours
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="details-grid">
                        <div class="details-main">
                            <section class="detail-section">
                                <h2><i class="fas fa-align-left"></i> Description</h2>
                                <p><?= nl2br(htmlspecialchars($projet['description'] ?? '')) ?></p>
                            </section>

                            <?php if (!empty($projet['objectifs'])): ?>
                                <section class="detail-section">
                                    <h2><i class="fas fa-bullseye"></i> Objectifs</h2>
                                    <p><?= nl2br(htmlspecialchars($projet['objectifs'])) ?></p>
                                </section>
                            <?php endif; ?>

                            <?php if (!empty($membres)): ?>
                                <section class="detail-section">
                                    <h2><i class="fas fa-users"></i> Équipe du projet</h2>
                                    <div class="membres-grid">
                                        <?php foreach ($membres as $membre): ?>
                                            <div class="membre-card-small">
                                                <div class="membre-photo">
                                                    <?php if ($membre['photo']): ?>
                                                        <img src="<?= UPLOADS_URL . 'photos/' . $membre['photo'] ?>"
                                                            alt="<?= htmlspecialchars($membre['nom']) ?>">
                                                    <?php else: ?>
                                                        <i class="fas fa-user"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="membre-info">
                                                    <h4><?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?></h4>
                                                    <p class="membre-role"><?= htmlspecialchars($membre['role_projet'] ?? 'Membre') ?>
                                                    </p>
                                                    <p class="membre-grade"><?= htmlspecialchars($membre['grade'] ?? '') ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>

                            <?php if (!empty($publications)): ?>
                                <section class="detail-section">
                                    <h2><i class="fas fa-file-alt"></i> Publications associées</h2>
                                    <div class="publications-list">
                                        <?php foreach ($publications as $pub): ?>
                                            <div class="publication-item">
                                                <span class="pub-type"><?= htmlspecialchars($pub['type']) ?></span>
                                                <h4><?= htmlspecialchars($pub['titre']) ?></h4>
                                                <p class="pub-authors"><?= htmlspecialchars($pub['auteurs']) ?></p>
                                                <p class="pub-year"><i class="fas fa-calendar"></i> <?= $pub['annee'] ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>
                        </div>

                        <div class="details-sidebar">
                            <div class="info-card">
                                <h3><i class="fas fa-info-circle"></i> Informations</h3>
                                <ul class="info-list">
                                    <li>
                                        <strong>Responsable :</strong>
                                        <span><?= htmlspecialchars(($projet['responsable_nom'] ?? '') . ' ' . ($projet['responsable_prenom'] ?? '')) ?></span>
                                    </li>
                                    <li>
                                        <strong>Financement :</strong>
                                        <span><?= htmlspecialchars($projet['type_financement_nom'] ?? 'Non défini') ?></span>
                                    </li>
                                    <?php if ($projet['budget']): ?>
                                        <li>
                                            <strong>Budget :</strong>
                                            <span><?= number_format($projet['budget'], 0, ',', ' ') ?> DZD</span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
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
     * Obtenir la classe CSS selon le statut
     */
    private function getStatusClass($statut)
    {
        $statut = strtolower($statut);
        if (strpos($statut, 'cours') !== false)
            return 'status-en-cours';
        if (strpos($statut, 'termin') !== false)
            return 'status-termine';
        if (strpos($statut, 'soumis') !== false)
            return 'status-soumis';
        return 'status-default';
    }
}
?>