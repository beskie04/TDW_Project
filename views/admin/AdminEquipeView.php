<?php
require_once __DIR__ . '/../BaseView.php';

// Import Generic Framework Components
require_once __DIR__ . '/../components/Table.php';
require_once __DIR__ . '/../components/Modal.php';
require_once __DIR__ . '/../components/ActionButtons.php';
require_once __DIR__ . '/../components/Button.php';
require_once __DIR__ . '/../components/Badge.php';
require_once __DIR__ . '/../components/Section.php';
require_once __DIR__ . '/../components/Grid.php';
require_once __DIR__ . '/../components/Card.php';
require_once __DIR__ . '/../components/Avatar.php';
require_once __DIR__ . '/../components/Alert.php';
require_once __DIR__ . '/../components/FormGroup.php';
require_once __DIR__ . '/../components/FormInput.php';
require_once __DIR__ . '/../components/FormActions.php';
require_once __DIR__ . '/../components/Breadcrumb.php';
require_once __DIR__ . '/../components/StatCard.php';

class AdminEquipeView extends BaseView
{
    public function __construct()
    {
        $this->currentPage = 'admin-equipes';
        $this->pageTitle = 'Gestion des Équipes';
    }

    /**
     * Page principale : Liste des équipes
     */
    public function renderIndex($equipes, $stats)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-users-cog"></i> Gestion des Équipes</h1>
                        <p class="subtitle">Gérer les équipes de recherche du laboratoire</p>
                    </div>
                    <?php
                    Button::render([
                        'text' => 'Créer une équipe',
                        'icon' => 'fas fa-plus',
                        'variant' => 'primary',
                        'href' => '?page=admin&section=equipes&action=create'
                    ]);
                    ?>
                </div>

                <!-- Statistics -->
                <?php if (!empty($stats)): ?>
                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                        <?php
                        StatCard::render([
                            'title' => 'Total Équipes',
                            'value' => $stats['total'],
                            'icon' => 'fas fa-users-cog',
                            'variant' => 'primary'
                        ]);

                        if (!empty($stats['plus_grande'])) {
                            StatCard::render([
                                'title' => 'Plus Grande Équipe',
                                'value' => $stats['plus_grande']['nom'],
                                'subtitle' => $stats['plus_grande']['nb_membres'] . ' membres',
                                'icon' => 'fas fa-crown',
                                'variant' => 'success'
                            ]);
                        }

                        StatCard::render([
                            'title' => 'Moyenne Membres',
                            'value' => $stats['moyenne_membres'],
                            'subtitle' => 'membres/équipe',
                            'icon' => 'fas fa-chart-line',
                            'variant' => 'info'
                        ]);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Teams Table -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Liste des Équipes</h3>
                    </div>
                    <div class="card-content" style="padding: 0;">
                        <?php
                        Table::render(
                            [
                                'headers' => ['#', 'Nom de l\'équipe', 'Chef d\'équipe', 'Membres', 'Actions'],
                                'rows' => $equipes,
                                'striped' => true,
                                'hoverable' => true
                            ],
                            function ($equipe, $index) {
                                ?>
                            <tr>
                                <td>
                                    <?= $index + 1 ?>
                                </td>
                                <td>
                                    <strong style="color: var(--primary-color);">
                                        <?= htmlspecialchars($equipe['nom']) ?>
                                    </strong>
                                    <?php if ($equipe['description']): ?>
                                        <br>
                                        <small style="color: var(--gray-600);">
                                            <?= htmlspecialchars(substr($equipe['description'], 0, 60)) ?>...
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($equipe['chef_nom']): ?>
                                        <?= htmlspecialchars($equipe['chef_nom'] . ' ' . $equipe['chef_prenom']) ?>
                                        <?php if ($equipe['chef_grade']): ?>
                                            <br>
                                            <small style="color: var(--gray-600);">
                                                <?= htmlspecialchars($equipe['chef_grade']) ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: var(--gray-400);">Non défini</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        Badge::render([
                                            'text' => $equipe['nb_membres'] . ' membre(s)',
                                            'variant' => 'info'
                                        ]);
                                        ?>
                                </td>
                                <td>
                                    <?php
                                        ActionButtons::render([
                                            [
                                                'type' => 'view',
                                                'href' => '?page=admin&section=equipes&action=details&id=' . $equipe['id'],
                                                'title' => 'Voir détails'
                                            ],
                                            [
                                                'type' => 'edit',
                                                'href' => '?page=admin&section=equipes&action=edit&id=' . $equipe['id'],
                                                'title' => 'Modifier'
                                            ],
                                            [
                                                'type' => 'delete',
                                                'onClick' => "confirmDelete({$equipe['id']}, '" . htmlspecialchars(addslashes($equipe['nom'])) . "')",
                                                'title' => 'Supprimer'
                                            ]
                                        ]);
                                        ?>
                                </td>
                            </tr>
                            <?php
                            }
                        );
                        ?>
                    </div>
                </div>
            </div>
        </main>

        <!-- Delete Confirmation Modal -->
        <?php
        Modal::render(
            [
                'id' => 'deleteModal',
                'title' => 'Confirmer la suppression',
                'size' => 'small'
            ],
            function () {
                ?>
            <p id="deleteMessage">Êtes-vous sûr de vouloir supprimer cette équipe ?</p>
            <form id="deleteForm" method="POST" action="?page=admin&section=equipes&action=delete">
                <input type="hidden" name="id" id="deleteId">
            </form>
            <?php
            },
            function () {
                ?>
            <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">
                Annuler
            </button>
            <button type="submit" form="deleteForm" class="btn btn-danger">
                <i class="fas fa-trash"></i> Supprimer
            </button>
            <?php
            }
        );
        ?>

        <script>
            function confirmDelete(id, nom) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteMessage').textContent =
                    `Êtes-vous sûr de vouloir supprimer l'équipe "${nom}" ?`;
                openModal('deleteModal');
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Page de détails d'une équipe
     */
    public function renderDetails($equipe, $membres, $ressources, $publications, $membresDisponibles)
    {
        $this->pageTitle = $equipe['nom'];
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Admin', 'url' => '?page=admin'],
                    ['text' => 'Équipes', 'url' => '?page=admin&section=equipes'],
                    ['text' => $equipe['nom']]
                ]);
                ?>

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-users-cog"></i>
                            <?= htmlspecialchars($equipe['nom']) ?>
                        </h1>
                    </div>
                    <div style="display: flex; gap: 0.75rem;">
                        <?php
                        Button::render([
                            'text' => 'Modifier',
                            'icon' => 'fas fa-edit',
                            'variant' => 'secondary',
                            'href' => '?page=admin&section=equipes&action=edit&id=' . $equipe['id']
                        ]);
                        Button::render([
                            'text' => 'Retour',
                            'icon' => 'fas fa-arrow-left',
                            'variant' => 'default',
                            'href' => '?page=admin&section=equipes'
                        ]);
                        ?>
                    </div>
                </div>

                <!-- Description -->
                <?php if ($equipe['description']): ?>
                    <?php
                    Section::render([
                        'title' => 'Description',
                        'icon' => 'fas fa-info-circle'
                    ], function () use ($equipe) {
                        echo '<div class="card"><div class="card-content">';
                        echo '<p style="line-height: 1.8; color: var(--gray-700);">';
                        echo nl2br(htmlspecialchars($equipe['description']));
                        echo '</p></div></div>';
                    });
                    ?>
                <?php endif; ?>

                <!-- Chef d'équipe -->
                <?php if ($equipe['chef_nom']): ?>
                    <?php
                    Section::render([
                        'title' => 'Chef d\'équipe',
                        'icon' => 'fas fa-user-tie'
                    ], function () use ($equipe) {
                        $photoUrl = $equipe['chef_photo'] ? UPLOADS_URL . 'photos/' . $equipe['chef_photo'] : null;
                        ?>
                        <div class="card">
                            <div class="card-content" style="padding: 1.5rem;">
                                <div style="display: flex; align-items: center; gap: 1.5rem;">
                                    <?php
                                    Avatar::render([
                                        'src' => $photoUrl,
                                        'alt' => $equipe['chef_nom'],
                                        'size' => 'large'
                                    ]);
                                    ?>
                                    <div>
                                        <h4 style="margin: 0 0 0.5rem 0; color: var(--dark-color);">
                                            <?= htmlspecialchars($equipe['chef_nom'] . ' ' . $equipe['chef_prenom']) ?>
                                        </h4>
                                        <?php if ($equipe['chef_grade']): ?>
                                            <p style="margin: 0 0 0.25rem 0; color: var(--gray-600);">
                                                <?= htmlspecialchars($equipe['chef_grade']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($equipe['chef_email']): ?>
                                            <p style="margin: 0; color: var(--gray-600);">
                                                <i class="fas fa-envelope"></i>
                                                <?= htmlspecialchars($equipe['chef_email']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    });
                    ?>
                <?php endif; ?>

                <!-- Membres de l'équipe -->
                <?php
                Section::render([
                    'title' => 'Membres de l\'équipe (' . count($membres) . ')',
                    'icon' => 'fas fa-users'
                ], function () use ($membres, $equipe, $membresDisponibles) {
                    ?>
                    <div class="card">
                        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                            <h3>Liste des membres</h3>
                            <?php if (!empty($membresDisponibles)): ?>
                                <?php
                                Button::render([
                                    'text' => 'Ajouter un membre',
                                    'icon' => 'fas fa-plus',
                                    'variant' => 'primary',
                                    'onClick' => "openModal('addMembreModal')"
                                ]);
                                ?>
                            <?php endif; ?>
                        </div>
                        <div class="card-content" style="padding: 0;">
                            <?php if (!empty($membres)): ?>
                                <?php
                                Table::render(
                                    [
                                        'headers' => ['Photo', 'Nom', 'Poste', 'Grade', 'Date d\'ajout', 'Actions'],
                                        'rows' => $membres,
                                        'striped' => true,
                                        'hoverable' => true
                                    ],
                                    function ($membre) use ($equipe) {
                                        $photoUrl = $membre['photo'] ? UPLOADS_URL . 'photos/' . $membre['photo'] : null;
                                        ?>
                                    <tr>
                                        <td>
                                            <?php
                                                Avatar::render([
                                                    'src' => $photoUrl,
                                                    'alt' => $membre['nom'],
                                                    'size' => 'small'
                                                ]);
                                                ?>
                                        </td>
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($membre['poste'] ?? '-') ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($membre['grade'] ?? '-') ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($membre['date_ajout'])) ?>
                                        </td>
                                        <td>
                                            <?php
                                                ActionButtons::render([
                                                    [
                                                        'type' => 'delete',
                                                        'onClick' => "confirmRemoveMembre({$equipe['id']}, {$membre['id_membre']}, '" . htmlspecialchars(addslashes($membre['nom'] . ' ' . $membre['prenom'])) . "')",
                                                        'title' => 'Retirer de l\'équipe'
                                                    ]
                                                ]);
                                                ?>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                );
                                ?>
                            <?php else: ?>
                                <div style="padding: 2rem; text-align: center; color: var(--gray-500);">
                                    <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                    <p>Aucun membre dans cette équipe</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                });
                ?>

                <!-- Ressources allouées -->
                <?php
                Section::render([
                    'title' => 'Ressources allouées (' . count($ressources) . ')',
                    'icon' => 'fas fa-laptop'
                ], function () use ($ressources) {
                    ?>
                    <div class="card">
                        <div class="card-content" style="padding: 0;">
                            <?php if (!empty($ressources)): ?>
                                <?php
                                Table::render(
                                    [
                                        'headers' => ['Équipement', 'Type', 'Réservé par', 'Date début', 'Date fin', 'Statut'],
                                        'rows' => $ressources,
                                        'striped' => true,
                                        'hoverable' => true
                                    ],
                                    function ($ressource) {
                                        ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($ressource['nom']) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($ressource['type'] ?? '-') ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($ressource['membre_nom'] . ' ' . $ressource['membre_prenom']) ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($ressource['date_debut'])) ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($ressource['date_fin'])) ?>
                                        </td>
                                        <td>
                                            <?php
                                                Badge::render([
                                                    'text' => ucfirst($ressource['statut']),
                                                    'variant' => $ressource['statut'] === 'active' ? 'success' : 'default'
                                                ]);
                                                ?>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                );
                                ?>
                            <?php else: ?>
                                <div style="padding: 2rem; text-align: center; color: var(--gray-500);">
                                    <i class="fas fa-laptop" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                    <p>Aucune ressource allouée actuellement</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                });
                ?>

                <!-- Publications -->
                <?php
                Section::render([
                    'title' => 'Publications de l\'équipe (' . count($publications) . ')',
                    'icon' => 'fas fa-file-alt'
                ], function () use ($publications) {
                    ?>
                    <div class="card">
                        <div class="card-content" style="padding: 0;">
                            <?php if (!empty($publications)): ?>
                                <?php
                                Table::render(
                                    [
                                        'headers' => ['Titre', 'Type', 'Auteurs', 'Année'],
                                        'rows' => $publications,
                                        'striped' => true,
                                        'hoverable' => true
                                    ],
                                    function ($pub) {
                                        ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($pub['titre']) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($pub['type'] ?? '-') ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($pub['auteurs'] ?? '-') ?>
                                        </td>
                                        <td>
                                            <?php
                                                Badge::render([
                                                    'text' => $pub['annee'],
                                                    'variant' => 'info'
                                                ]);
                                                ?>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                );
                                ?>
                            <?php else: ?>
                                <div style="padding: 2rem; text-align: center; color: var(--gray-500);">
                                    <i class="fas fa-file-alt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                    <p>Aucune publication</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                });
                ?>
            </div>
        </main>

        <!-- Modal Ajouter Membre -->
        <?php if (!empty($membresDisponibles)): ?>
            <?php
            Modal::render(
                [
                    'id' => 'addMembreModal',
                    'title' => 'Ajouter un membre à l\'équipe',
                    'size' => 'medium'
                ],
                function () use ($membresDisponibles, $equipe) {
                    ?>
                <form id="addMembreForm" method="POST" action="?page=admin&section=equipes&action=addMembre">
                    <input type="hidden" name="equipe_id" value="<?= $equipe['id'] ?>">

                    <?php
                        FormGroup::render([
                            'label' => 'Sélectionner un membre',
                            'required' => true
                        ], function () use ($membresDisponibles) {
                            ?>
                        <select name="membre_id" class="form-control" required>
                            <option value="">-- Choisir un membre --</option>
                            <?php foreach ($membresDisponibles as $membre): ?>
                                <option value="<?= $membre['id_membre'] ?>">
                                    <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                                    <?php if ($membre['poste']): ?>
                                        -
                                        <?= htmlspecialchars($membre['poste']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php
                        });
                        ?>
                </form>
                <?php
                },
                function () {
                    ?>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addMembreModal')">
                    Annuler
                </button>
                <button type="submit" form="addMembreForm" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter
                </button>
                <?php
                }
            );
        ?>
        <?php endif; ?>

        <!-- Modal Retirer Membre -->
        <?php
        Modal::render(
            [
                'id' => 'removeMembreModal',
                'title' => 'Confirmer le retrait',
                'size' => 'small'
            ],
            function () {
                ?>
            <p id="removeMembreMessage">Êtes-vous sûr de vouloir retirer ce membre de l'équipe ?</p>
            <form id="removeMembreForm" method="POST" action="?page=admin&section=equipes&action=removeMembre">
                <input type="hidden" name="equipe_id" id="removeEquipeId">
                <input type="hidden" name="membre_id" id="removeMembreId">
            </form>
            <?php
            },
            function () {
                ?>
            <button type="button" class="btn btn-secondary" onclick="closeModal('removeMembreModal')">
                Annuler
            </button>
            <button type="submit" form="removeMembreForm" class="btn btn-danger">
                <i class="fas fa-user-minus"></i> Retirer
            </button>
            <?php
            }
        );
        ?>

        <script>
            function confirmRemoveMembre(equipeId, membreId, nom) {
                document.getElementById('removeEquipeId').value = equipeId;
                document.getElementById('removeMembreId').value = membreId;
                document.getElementById('removeMembreMessage').textContent =
                    `Êtes-vous sûr de vouloir retirer "${nom}" de cette équipe ?`;
                openModal('removeMembreModal');
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Page de création d'une équipe
     */
    public function renderCreate($membres)
    {
        $this->pageTitle = 'Créer une équipe';
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Admin', 'url' => '?page=admin'],
                    ['text' => 'Équipes', 'url' => '?page=admin&section=equipes'],
                    ['text' => 'Créer']
                ]);
                ?>

                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-plus"></i> Créer une nouvelle équipe</h1>
                </div>

                <!-- Form -->
                <div class="card">
                    <div class="card-content">
                        <form method="POST" action="?page=admin&section=equipes&action=store">
                            <?php
                            FormGroup::render([
                                'label' => 'Nom de l\'équipe',
                                'required' => true
                            ], function () {
                                FormInput::render([
                                    'type' => 'text',
                                    'name' => 'nom',
                                    'placeholder' => 'Ex: Équipe Intelligence Artificielle',
                                    'required' => true
                                ]);
                            });

                            FormGroup::render([
                                'label' => 'Chef d\'équipe',
                                'required' => false
                            ], function () use ($membres) {
                                ?>
                                <select name="chef_id" class="form-control">
                                    <option value="">-- Aucun (à définir plus tard) --</option>
                                    <?php foreach ($membres as $membre): ?>
                                        <option value="<?= $membre['id_membre'] ?>">
                                            <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                                            <?php if ($membre['grade']): ?>
                                                -
                                                <?= htmlspecialchars($membre['grade']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php
                            });

                            FormGroup::render([
                                'label' => 'Description',
                                'required' => false
                            ], function () {
                                ?>
                                <textarea name="description" class="form-control" rows="5"
                                    placeholder="Description de l'équipe et de ses thématiques de recherche"></textarea>
                                <?php
                            });

                            FormActions::render(['align' => 'right'], function () {
                                ?>
                                <a href="?page=admin&section=equipes" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Créer l'équipe
                                </button>
                                <?php
                            });
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Page de modification d'une équipe
     */
    public function renderEdit($equipe, $membres)
    {
        $this->pageTitle = 'Modifier ' . $equipe['nom'];
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Breadcrumb -->
                <?php
                Breadcrumb::render([
                    ['text' => 'Admin', 'url' => '?page=admin'],
                    ['text' => 'Équipes', 'url' => '?page=admin&section=equipes'],
                    ['text' => $equipe['nom'], 'url' => '?page=admin&section=equipes&action=details&id=' . $equipe['id']],
                    ['text' => 'Modifier']
                ]);
                ?>

                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-edit"></i> Modifier l'équipe</h1>
                </div>

                <!-- Form -->
                <div class="card">
                    <div class="card-content">
                        <form method="POST" action="?page=admin&section=equipes&action=update">
                            <input type="hidden" name="id" value="<?= $equipe['id'] ?>">

                            <?php
                            FormGroup::render([
                                'label' => 'Nom de l\'équipe',
                                'required' => true
                            ], function () use ($equipe) {
                                FormInput::render([
                                    'type' => 'text',
                                    'name' => 'nom',
                                    'value' => $equipe['nom'],
                                    'required' => true
                                ]);
                            });

                            FormGroup::render([
                                'label' => 'Chef d\'équipe',
                                'required' => false
                            ], function () use ($membres, $equipe) {
                                ?>
                                <select name="chef_id" class="form-control">
                                    <option value="">-- Aucun --</option>
                                    <?php foreach ($membres as $membre): ?>
                                        <option value="<?= $membre['id_membre'] ?>" <?= $membre['id_membre'] == $equipe['chef_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?>
                                            <?php if ($membre['grade']): ?>
                                                -
                                                <?= htmlspecialchars($membre['grade']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php
                            });

                            FormGroup::render([
                                'label' => 'Description',
                                'required' => false
                            ], function () use ($equipe) {
                                ?>
                                <textarea name="description" class="form-control"
                                    rows="5"><?= htmlspecialchars($equipe['description'] ?? '') ?></textarea>
                                <?php
                            });

                            FormActions::render(['align' => 'right'], function () {
                                ?>
                                <a href="?page=admin&section=equipes" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                                <?php
                            });
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }
}
?>