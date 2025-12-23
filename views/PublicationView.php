<?php
require_once __DIR__ . '/BaseView.php';

class PublicationView extends BaseView
{

    public function __construct()
    {
        $this->currentPage = 'publications';
        $this->pageTitle = 'Publications';
    }

    /**
     * Afficher la liste des publications
     */
    public function renderListe($publications, $years, $types, $domaines, $auteurs)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="page-header">
                    <h1><i class="fas fa-file-alt"></i> Base Documentaire et Publications</h1>
                    <p class="subtitle">Consultez les publications du laboratoire</p>
                </div>

                <!-- Filtres -->
                <div class="filters-section">
                    <div class="filters-wrapper">
                        <div class="filter-group">
                            <label><i class="fas fa-calendar"></i> Année</label>
                            <select id="filter-annee" class="filter-select">
                                <option value="">Toutes les années</option>
                                <?php foreach ($years as $y): ?>
                                    <option value="<?= $y['annee'] ?>"><?= $y['annee'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

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
                            <label><i class="fas fa-layer-group"></i> Domaine</label>
                            <select id="filter-domaine" class="filter-select">
                                <option value="">Tous les domaines</option>
                                <?php foreach ($domaines as $dom): ?>
                                    <option value="<?= $dom['id_thematique'] ?>">
                                        <?= htmlspecialchars($dom['nom_thematique']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-user"></i> Auteur</label>
                            <select id="filter-auteur" class="filter-select">
                                <option value="">Tous les auteurs</option>
                                <?php foreach ($auteurs as $auteur): ?>
                                    <option value="<?= htmlspecialchars($auteur) ?>">
                                        <?= htmlspecialchars($auteur) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group search-group">
                            <label><i class="fas fa-search"></i> Recherche avancée</label>
                            <input type="text" id="search-input" placeholder="Titre, auteurs, résumé..." class="search-input">
                        </div>

                        <button id="reset-filters" class="btn-reset">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </button>
                    </div>
                </div>

                <!-- Liste des publications -->
                <div id="publications-container" class="publications-list">
                    <?php $this->renderPublicationsList($publications); ?>
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
     * Afficher la liste des publications 
     */
    public function renderPublicationsList($publications)
    {
        if (empty($publications)) {
            echo '<div class="no-results"><i class="fas fa-inbox"></i><p>Aucune publication trouvée</p></div>';
            return;
        }

        foreach ($publications as $pub) {
            $this->renderPublicationCard($pub);
        }
    }

    /**
     * Afficher une carte de publication
     */
    private function renderPublicationCard($pub)
    {
        ?>
        <div class="publication-card">
            <div class="publication-header">
                <span class="pub-type"><?= htmlspecialchars(strtoupper($pub['type'])) ?></span>
                <span class="pub-year"><?= $pub['annee'] ?></span>
            </div>

            <h3 class="publication-titre"><?= htmlspecialchars($pub['titre']) ?></h3>

            <p class="pub-authors">
                <i class="fas fa-user"></i> <?= htmlspecialchars($pub['auteurs']) ?>
            </p>

            <?php if (!empty($pub['resume'])): ?>
                <p class="pub-resume">
                    <?= htmlspecialchars(mb_substr($pub['resume'], 0, 200)) ?>...
                </p>
            <?php endif; ?>

            <div class="publication-meta">
                <?php if (!empty($pub['doi'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-link"></i>
                        <span>DOI: <?= htmlspecialchars($pub['doi']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pub['domaine_nom'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-layer-group"></i>
                        <span>Domaine: <?= htmlspecialchars($pub['domaine_nom']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pub['fichier'])): ?>
                    <div class="meta-item">
                        <a href="<?= UPLOADS_URL . 'publications/' . $pub['fichier'] ?>" class="btn-download" target="_blank">
                            <i class="fas fa-download"></i> Télécharger
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
?>