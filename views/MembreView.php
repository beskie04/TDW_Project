<?php
require_once __DIR__ . '/BaseView.php';

class MembreView extends BaseView
{

    public function __construct()
    {
        $this->currentPage = 'membres';
        $this->pageTitle = 'Membres et Équipes';
    }

    /**
     * Page principale : Présentation, Organigramme et Équipes
     */
    public function renderIndex($equipes, $directeur)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="page-header">
                    <h1><i class="fas fa-users"></i> Présentation, Organigramme et Équipes</h1>
                </div>

                <!-- Présentation du laboratoire -->
                <section class="lab-presentation">
                    <h2><i class="fas fa-flask"></i> Présentation du Laboratoire</h2>
                    <p>
                        Le Laboratoire de Recherche en Informatique de l'École Supérieure d'Informatique est un centre
                        d'excellence
                        dédié à l'innovation et à la recherche de pointe dans divers domaines de l'informatique. Nos équipes
                        travaillent
                        sur des problématiques actuelles telles que l'intelligence artificielle, la cybersécurité, le cloud
                        computing,
                        les réseaux et les systèmes embarqués.
                    </p>
                    <p>
                        Fort d'une équipe de chercheurs expérimentés et de doctorants talentueux, le laboratoire collabore avec
                        des partenaires académiques et industriels nationaux et internationaux pour produire des résultats de
                        recherche
                        de haut niveau et former la prochaine génération d'experts en informatique.
                    </p>
                </section>

                <!-- Organigramme -->
                <section class="organigramme-section">
                    <h2><i class="fas fa-sitemap"></i> Organigramme du Laboratoire</h2>

                    <?php if ($directeur): ?>
                        <div class="directeur-card">
                            <div class="directeur-photo">
                                <?php if ($directeur['photo']): ?>
                                    <img src="<?= UPLOADS_URL . 'photos/' . $directeur['photo'] ?>"
                                        alt="<?= htmlspecialchars($directeur['nom']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="directeur-info">
                                <h3><?= htmlspecialchars($directeur['nom'] . ' ' . $directeur['prenom']) ?></h3>
                                <p class="directeur-poste"><?= htmlspecialchars($directeur['poste']) ?></p>
                                <p class="directeur-grade"><?= htmlspecialchars($directeur['grade']) ?></p>
                                <p class="directeur-email"><i class="fas fa-envelope"></i>
                                    <?= htmlspecialchars($directeur['email']) ?></p>
                                <div class="directeur-actions">
                                    <a href="?page=membres&action=biographie&id=<?= $directeur['id_membre'] ?>"
                                        class="btn-secondary">
                                        <i class="fas fa-user"></i> Biographie
                                    </a>
                                    <a href="?page=membres&action=publications&id=<?= $directeur['id_membre'] ?>"
                                        class="btn-secondary">
                                        <i class="fas fa-file-alt"></i> Publications
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Équipes de recherche -->
                <section class="equipes-section">
                    <h2><i class="fas fa-users-cog"></i> Équipes de Recherche</h2>

                    <div class="equipes-grid">
                        <?php foreach ($equipes as $equipe): ?>
                            <?php $this->renderEquipeCard($equipe); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Carte d'équipe
     */
    private function renderEquipeCard($equipe)
    {
        ?>
        <div class="equipe-card">
            <div class="equipe-header">
                <h3><?= htmlspecialchars($equipe['nom']) ?></h3>
                <span class="membre-count"><?= $equipe['nb_membres'] ?> membres</span>
            </div>

            <p class="equipe-description"><?= htmlspecialchars($equipe['description'] ?? '') ?></p>

            <?php if ($equipe['chef_nom']): ?>
                <div class="equipe-chef">
                    <div class="chef-photo-small">
                        <?php if ($equipe['chef_photo']): ?>
                            <img src="<?= UPLOADS_URL . 'photos/' . $equipe['chef_photo'] ?>"
                                alt="<?= htmlspecialchars($equipe['chef_nom']) ?>">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="chef-info">
                        <strong>Chef d'équipe :</strong>
                        <p><?= htmlspecialchars($equipe['chef_nom'] . ' ' . $equipe['chef_prenom']) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="equipe-actions">
                <a href="?page=membres&action=equipe&id=<?= $equipe['id'] ?>" class="btn-primary">
                    <i class="fas fa-arrow-right"></i> Voir l'équipe
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Page détails d'une équipe
     */
    public function renderEquipe($equipe, $membres, $publications)
    {
        $this->pageTitle = $equipe['nom'];
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="breadcrumb">
                    <a href="?page=accueil">Accueil</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=membres">Membres</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= htmlspecialchars($equipe['nom']) ?></span>
                </div>

                <div class="equipe-details">
                    <div class="equipe-details-header">
                        <h1><?= htmlspecialchars($equipe['nom']) ?></h1>
                        <p class="equipe-description"><?= htmlspecialchars($equipe['description'] ?? '') ?></p>
                    </div>

                    <!-- Chef d'équipe -->
                    <?php if ($equipe['chef_nom']): ?>
                        <div class="chef-section">
                            <h2><i class="fas fa-user-tie"></i> Chef d'équipe</h2>
                            <div class="membre-card-horizontal">
                                <div class="membre-photo">
                                    <?php if ($equipe['chef_photo']): ?>
                                        <img src="<?= UPLOADS_URL . 'photos/' . $equipe['chef_photo'] ?>"
                                            alt="<?= htmlspecialchars($equipe['chef_nom']) ?>">
                                    <?php else: ?>
                                        <i class="fas fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="membre-info">
                                    <h3><?= htmlspecialchars($equipe['chef_nom'] . ' ' . $equipe['chef_prenom']) ?></h3>
                                    <p class="membre-grade"><?= htmlspecialchars($equipe['chef_grade'] ?? '') ?></p>
                                    <p class="membre-email"><i class="fas fa-envelope"></i>
                                        <?= htmlspecialchars($equipe['chef_email']) ?></p>
                                    <div class="membre-actions">
                                        <a href="?page=membres&action=biographie&id=<?= $equipe['chef_id'] ?>"
                                            class="btn-secondary">
                                            <i class="fas fa-user"></i> Biographie
                                        </a>
                                        <a href="?page=membres&action=publications&id=<?= $equipe['chef_id'] ?>"
                                            class="btn-secondary">
                                            <i class="fas fa-file-alt"></i> Publications
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Membres de l'équipe -->
                    <div class="membres-section">
                        <h2><i class="fas fa-users"></i> Membres de l'équipe</h2>
                        <div class="membres-grid">
                            <?php foreach ($membres as $membre): ?>
                                <?php if ($membre['id_membre'] != $equipe['chef_id']): // Ne pas afficher le chef deux fois ?>
                                    <?php $this->renderMembreCard($membre); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Publications de l'équipe -->
                    <?php if (!empty($publications)): ?>
                        <div class="publications-section">
                            <h2><i class="fas fa-file-alt"></i> Publications de l'équipe</h2>
                            <a href="?page=publications&equipe=<?= $equipe['id'] ?>" class="btn-primary">
                                <i class="fas fa-arrow-right"></i> Voir toutes les publications
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Carte de membre
     */
    private function renderMembreCard($membre)
    {
        ?>
        <div class="membre-card">
            <div class="membre-photo">
                <?php if ($membre['photo']): ?>
                    <img src="<?= UPLOADS_URL . 'photos/' . $membre['photo'] ?>" alt="<?= htmlspecialchars($membre['nom']) ?>">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="membre-info">
                <h4><?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?></h4>
                <p class="membre-poste"><?= htmlspecialchars($membre['poste'] ?? '') ?></p>
                <p class="membre-grade"><?= htmlspecialchars($membre['grade'] ?? '') ?></p>
                <div class="membre-actions">
                    <a href="?page=membres&action=biographie&id=<?= $membre['id_membre'] ?>" title="Biographie">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="?page=membres&action=publications&id=<?= $membre['id_membre'] ?>" title="Publications">
                        <i class="fas fa-file-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Page biographie d'un membre
     */
    public function renderBiographie($membre, $equipes, $publications)
    {
        $this->pageTitle = $membre['nom'] . ' ' . $membre['prenom'];
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="breadcrumb">
                    <a href="?page=accueil">Accueil</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=membres">Membres</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?></span>
                </div>

                <div class="membre-biographie">
                    <div class="bio-header">
                        <div class="bio-photo">
                            <?php if ($membre['photo']): ?>
                                <img src="<?= UPLOADS_URL . 'photos/' . $membre['photo'] ?>"
                                    alt="<?= htmlspecialchars($membre['nom']) ?>">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="bio-info">
                            <h1><?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?></h1>
                            <p class="bio-poste"><?= htmlspecialchars($membre['poste'] ?? '') ?></p>
                            <p class="bio-grade"><?= htmlspecialchars($membre['grade'] ?? '') ?></p>
                            <p class="bio-email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($membre['email']) ?></p>
                        </div>
                    </div>

                    <div class="bio-content">
                        <?php if ($membre['biographie']): ?>
                            <section class="bio-section">
                                <h2><i class="fas fa-user"></i> Biographie</h2>
                                <p><?= nl2br(htmlspecialchars($membre['biographie'])) ?></p>
                            </section>
                        <?php endif; ?>

                        <?php if ($membre['domaine_recherche']): ?>
                            <section class="bio-section">
                                <h2><i class="fas fa-search"></i> Domaines de recherche</h2>
                                <p><?= nl2br(htmlspecialchars($membre['domaine_recherche'])) ?></p>
                            </section>
                        <?php endif; ?>

                        <?php if (!empty($equipes)): ?>
                            <section class="bio-section">
                                <h2><i class="fas fa-users"></i> Équipes</h2>
                                <div class="equipes-list">
                                    <?php foreach ($equipes as $eq): ?>
                                        <a href="?page=membres&action=equipe&id=<?= $eq['id'] ?>" class="equipe-badge">
                                            <?= htmlspecialchars($eq['nom']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php endif; ?>

                        <section class="bio-section">
                            <h2><i class="fas fa-file-alt"></i> Publications (<?= count($publications) ?>)</h2>
                            <a href="?page=membres&action=publications&id=<?= $membre['id_membre'] ?>" class="btn-primary">
                                Voir toutes les publications
                            </a>
                        </section>
                    </div>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Page publications d'un membre
     */
    public function renderPublications($membre, $publications)
    {
        $this->pageTitle = 'Publications de ' . $membre['nom'] . ' ' . $membre['prenom'];
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <div class="breadcrumb">
                    <a href="?page=accueil">Accueil</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=membres">Membres</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=membres&action=biographie&id=<?= $membre['id_membre'] ?>">
                        <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                    </a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Publications</span>
                </div>

                <div class="page-header">
                    <h1><i class="fas fa-file-alt"></i> Publications de
                        <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?></h1>
                    <p class="subtitle"><?= count($publications) ?> publication(s)</p>
                </div>

                <div class="publications-list">
                    <?php
                    require_once __DIR__ . '/PublicationView.php';
                    $pubView = new PublicationView();
                    $pubView->renderPublicationsList($publications);
                    ?>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }
}
?>