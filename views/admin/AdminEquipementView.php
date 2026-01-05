<?php
require_once __DIR__ . '/../BaseView.php';

// Import Components
require_once __DIR__ . '/../components/Button.php';
require_once __DIR__ . '/../components/Badge.php';
require_once __DIR__ . '/../components/Table.php';
require_once __DIR__ . '/../components/StatCard.php';
require_once __DIR__ . '/../components/Section.php';
require_once __DIR__ . '/../components/Modal.php';
require_once __DIR__ . '/../components/FormGroup.php';
require_once __DIR__ . '/../components/FormInput.php';
require_once __DIR__ . '/../components/FormActions.php';

class AdminEquipementView extends BaseView
{
    public function __construct()
    {
        $this->pageTitle = 'Administration - Équipements';
        $this->currentPage = 'admin';
    }

    /**
     * Liste des équipements (SANS réservations récentes)
     */
    public function renderListe($equipements, $stats)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <!-- Admin Header -->
                <div class="admin-header">
                    <h1><i class="fas fa-tools"></i> Gestion des Équipements</h1>
                    <div style="display: flex; gap: 1rem;">
                        <?php
                        Button::render([
                            'text' => 'Historique',
                            'icon' => 'fas fa-history',
                            'variant' => 'secondary',
                            'href' => '?page=admin&section=equipements&action=historique'
                        ]);

                        Button::render([
                            'text' => 'Demandes prioritaires',
                            'icon' => 'fas fa-star',
                            'variant' => 'warning',
                            'href' => '?page=admin&section=equipements&action=demandes'
                        ]);

                        Button::render([
                            'text' => 'Générer rapport PDF',
                            'icon' => 'fas fa-file-pdf',
                            'variant' => 'danger',
                            'href' => '?page=admin&section=equipements&action=genererRapport'
                        ]);

                        Button::render([
                            'text' => 'Nouvel Équipement',
                            'icon' => 'fas fa-plus',
                            'variant' => 'primary',
                            'href' => '?page=admin&section=equipements&action=create'
                        ]);
                        ?>
                    </div>
                </div>

                <!-- Statistics Grid -->
                <div class="stats-grid">
                    <?php
                    StatCard::render([
                        'value' => $stats['total'],
                        'label' => 'Total Équipements',
                        'icon' => 'fas fa-tools',
                        'color' => 'var(--primary-color, #2563eb)'
                    ]);

                    StatCard::render([
                        'value' => $stats['en_utilisation'],
                        'label' => 'En utilisation',
                        'icon' => 'fas fa-check-circle',
                        'color' => '#10b981'
                    ]);

                    foreach ($stats['par_etat'] as $stat) {
                        StatCard::render([
                            'value' => $stat['total'],
                            'label' => ETATS_EQUIPEMENTS[$stat['etat']] ?? $stat['etat'],
                            'icon' => 'fas fa-circle',
                            'color' => $this->getEtatColor($stat['etat'])
                        ]);
                    }
                    ?>
                </div>

                <!-- Equipment Table -->
                <h2 style="margin: 2rem 0 1rem 0;"><i class="fas fa-list"></i> Liste des équipements</h2>
                <?php
                Table::render([
                    'headers' => ['ID', 'Nom', 'Type', 'État', 'Description', 'Réservations', 'Actions'],
                    'rows' => $equipements,
                    'striped' => true,
                    'hoverable' => true
                ], function ($eq) {
                    ?>
                    <tr>
                        <td>
                            <?= $eq['id'] ?>
                        </td>
                        <td><strong>
                                <?= htmlspecialchars($eq['nom']) ?>
                            </strong></td>
                        <td>
                            <?php
                            Badge::render([
                                'text' => TYPES_EQUIPEMENTS[$eq['type']] ?? $eq['type'],
                                'variant' => 'primary',
                                'size' => 'small'
                            ]);
                            ?>
                        </td>
                        <td>
                            <?php
                            Badge::render([
                                'text' => ETATS_EQUIPEMENTS[$eq['etat']] ?? $eq['etat'],
                                'variant' => $this->getEtatBadgeVariant($eq['etat']),
                                'size' => 'small'
                            ]);
                            ?>
                        </td>
                        <td>
                            <?= htmlspecialchars(mb_substr($eq['description'] ?? '', 0, 50)) ?>...
                        </td>
                        <td>
                            <?= $eq['nb_reservations_actives'] ?>
                        </td>
                        <td style="white-space: nowrap;">
                            <a href="?page=admin&section=equipements&action=details&id=<?= $eq['id'] ?>" class="btn btn-sm btn-info"
                                title="Voir détails">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="?page=admin&section=equipements&action=edit&id=<?= $eq['id'] ?>" class="btn btn-sm btn-primary"
                                title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmDelete(<?= $eq['id'] ?>, '<?= htmlspecialchars(addslashes($eq['nom'])) ?>')"
                                class="btn btn-sm btn-danger" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php
                });
                ?>
            </div>
        </main>

        <script>
            function confirmDelete(id, nom) {
                if (confirm(`Êtes-vous sûr de vouloir supprimer "${nom}" ?`)) {
                    window.location.href = '?page=admin&section=equipements&action=delete&id=' + id;
                }
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Historique des réservations
     */
    public function renderHistorique($reservations)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-history"></i> Historique des Réservations</h1>
                    <Button::render([ 'text'=> 'Retour',
                        'icon' => 'fas fa-arrow-left',
                        'variant' => 'secondary',
                        'href' => '?page=admin&section=equipements'
                        ]);
                </div>

                <?php
                Table::render([
                    'headers' => ['Équipement', 'Membre', 'Début', 'Fin', 'Statut', 'Créée le', 'Actions'],
                    'rows' => $reservations
                ], function ($res) {
                    ?>
                    <tr>
                        <td><strong>
                                <?= htmlspecialchars($res['equipement_nom']) ?>
                            </strong></td>
                        <td>
                            <?= htmlspecialchars($res['membre_nom'] . ' ' . $res['membre_prenom']) ?>
                        </td>
                        <td>
                            <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?>
                        </td>
                        <td>
                            <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?>
                        </td>
                        <td>
                            <?php
                            Badge::render([
                                'text' => ucfirst($res['statut']),
                                'variant' => $res['statut'] === 'active' ? 'success' : 'default',
                                'size' => 'small'
                            ]);
                            ?>
                        </td>
                        <td>
                            <?= date('d/m/Y H:i', strtotime($res['created_at'])) ?>
                        </td>
                        <td>
                            <?php if ($res['statut'] === 'active'): ?>
                                <button onclick="annulerReservation(<?= $res['id'] ?>)" class="btn btn-sm btn-danger">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                });
                ?>
            </div>
        </main>

        <script>
            function annulerReservation(id) {
                if (confirm('Annuler cette réservation ?')) {
                    window.location.href = '?page=admin&section=equipements&action=annuler_reservation&id=' + id;
                }
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Gestion des demandes prioritaires
     */
    public function renderDemandes($demandes)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-star"></i> Demandes Prioritaires</h1>
                    <Button::render([ 'text'=> 'Retour',
                        'icon' => 'fas fa-arrow-left',
                        'variant' => 'secondary',
                        'href' => '?page=admin&section=equipements'
                        ]);
                </div>

                <?php if (empty($demandes)): ?>
                    <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px;">
                        <i class="fas fa-inbox" style="font-size: 4rem; color: var(--gray-400);"></i>
                        <h3 style="color: var(--gray-600); margin: 1rem 0 0 0;">Aucune demande prioritaire</h3>
                    </div>
                <?php else: ?>
                    <?php
                    Table::render([
                        'headers' => ['Équipement', 'Membre', 'Période', 'Justification', 'Statut', 'Date', 'Actions'],
                        'rows' => $demandes
                    ], function ($demande) {
                        ?>
                        <tr>
                            <td><strong>
                                    <?= htmlspecialchars($demande['equipement_nom']) ?>
                                </strong></td>
                            <td>
                                <?= htmlspecialchars($demande['membre_nom'] . ' ' . $demande['membre_prenom']) ?><br>
                                <small style="color: var(--gray-600);">
                                    <?= htmlspecialchars($demande['membre_email']) ?>
                                </small>
                            </td>
                            <td style="white-space: nowrap;">
                                <?= date('d/m/Y H:i', strtotime($demande['date_debut'])) ?><br>
                                <small>
                                    <?= date('d/m/Y H:i', strtotime($demande['date_fin'])) ?>
                                </small>
                            </td>
                            <td style="max-width: 300px;">
                                <?= htmlspecialchars(mb_substr($demande['justification'], 0, 100)) ?>...
                            </td>
                            <td>
                                <?php
                                $variants = [
                                    'en_attente' => 'warning',
                                    'approuvee' => 'success',
                                    'rejetee' => 'danger'
                                ];
                                Badge::render([
                                    'text' => ucfirst(str_replace('_', ' ', $demande['statut'])),
                                    'variant' => $variants[$demande['statut']] ?? 'default',
                                    'size' => 'small'
                                ]);
                                ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($demande['created_at'])) ?>
                            </td>
                            <td style="white-space: nowrap;">
                                <?php if ($demande['statut'] === 'en_attente'): ?>
                                    <button onclick="openModal('modal-approuver-<?= $demande['id'] ?>')" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="openModal('modal-rejeter-<?= $demande['id'] ?>')" class="btn btn-sm btn-danger">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Modal Approuver -->
                        <?php if ($demande['statut'] === 'en_attente'): ?>
                            <?php
                            Modal::render([
                                'id' => 'modal-approuver-' . $demande['id'],
                                'title' => 'Approuver la demande',
                                'size' => 'medium'
                            ], function () use ($demande) {
                                ?>
                                <form method="POST" action="?page=admin&section=equipements&action=approuverDemande">
                                    <input type="hidden" name="id" value="<?= $demande['id'] ?>">

                                    <p><strong>Membre:</strong>
                                        <?= htmlspecialchars($demande['membre_nom'] . ' ' . $demande['membre_prenom']) ?>
                                    </p>
                                    <p><strong>Équipement:</strong>
                                        <?= htmlspecialchars($demande['equipement_nom']) ?>
                                    </p>
                                    <p><strong>Justification:</strong>
                                        <?= htmlspecialchars($demande['justification']) ?>
                                    </p>

                                    <?php
                                    FormGroup::render([
                                        'label' => 'Réponse (optionnel)'
                                    ], function () {
                                        ?>
                                        <textarea name="reponse" class="form-control" rows="3" placeholder="Message pour le membre..."></textarea>
                                        <?php
                                    });
                                    ?>

                                    <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                                        <button type="button" onclick="closeModal('modal-approuver-<?= $demande['id'] ?>')"
                                            class="btn btn-secondary">Annuler</button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                    </div>
                                </form>
                                <?php
                            });

                            // Modal Rejeter
                            Modal::render([
                                'id' => 'modal-rejeter-' . $demande['id'],
                                'title' => 'Rejeter la demande',
                                'size' => 'medium'
                            ], function () use ($demande) {
                                ?>
                                <form method="POST" action="?page=admin&section=equipements&action=rejeterDemande">
                                    <input type="hidden" name="id" value="<?= $demande['id'] ?>">

                                    <p><strong>Membre:</strong>
                                        <?= htmlspecialchars($demande['membre_nom'] . ' ' . $demande['membre_prenom']) ?>
                                    </p>
                                    <p><strong>Équipement:</strong>
                                        <?= htmlspecialchars($demande['equipement_nom']) ?>
                                    </p>

                                    <?php
                                    FormGroup::render([
                                        'label' => 'Raison du rejet',
                                        'required' => true
                                    ], function () {
                                        ?>
                                        <textarea name="reponse" class="form-control" rows="3"
                                            placeholder="Expliquez pourquoi vous rejetez cette demande..." required></textarea>
                                        <?php
                                    });
                                    ?>

                                    <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                                        <button type="button" onclick="closeModal('modal-rejeter-<?= $demande['id'] ?>')"
                                            class="btn btn-secondary">Annuler</button>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-times"></i> Rejeter
                                        </button>
                                    </div>
                                </form>
                                <?php
                            });
                            ?>
                        <?php endif; ?>
                        <?php
                    });
                    ?>
                <?php endif; ?>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    /**
     * Détails équipement (ADMIN VIEW - pas de bouton réserver!)
     */
    public function renderDetails($equipement, $reservations, $stats)
    {
        $this->renderHeader();
        ?>

        <main class="admin-wrapper">
            <div class="container">
                <div class="admin-header">
                    <h1><i class="fas fa-eye"></i> Détails de l'équipement</h1>
                    <div style="display: flex; gap: 1rem;">
                        <Button::render([ 'text'=> 'Modifier',
                            'icon' => 'fas fa-edit',
                            'variant' => 'primary',
                            'href' => '?page=admin&section=equipements&action=edit&id=' . $equipement['id']
                            ]);
                            <Button::render([ 'text'=> 'Retour',
                                'icon' => 'fas fa-arrow-left',
                                'variant' => 'secondary',
                                'href' => '?page=admin&section=equipements'
                                ]);
                    </div>
                </div>

                <div style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
                    <h2>
                        <?= htmlspecialchars($equipement['nom']) ?>
                    </h2>
                    <div style="display: flex; gap: 1rem; margin: 1rem 0;">
                        <?php
                        Badge::render([
                            'text' => TYPES_EQUIPEMENTS[$equipement['type']] ?? $equipement['type'],
                            'variant' => 'primary'
                        ]);
                        Badge::render([
                            'text' => ETATS_EQUIPEMENTS[$equipement['etat']] ?? $equipement['etat'],
                            'variant' => $this->getEtatBadgeVariant($equipement['etat'])
                        ]);
                        ?>
                    </div>
                    <p><strong>Description:</strong>
                        <?= nl2br(htmlspecialchars($equipement['description'])) ?>
                    </p>
                    <?php if ($equipement['specifications']): ?>
                        <p><strong>Spécifications:</strong>
                            <?= nl2br(htmlspecialchars($equipement['specifications'])) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Stats -->
                <div class="stats-grid" style="margin-bottom: 2rem;">
                    <?php
                    StatCard::render([
                        'value' => $stats['total_reservations'],
                        'label' => 'Total Réservations',
                        'icon' => 'fas fa-calendar'
                    ]);
                    StatCard::render([
                        'value' => $stats['reservations_actives'],
                        'label' => 'Réservations Actives',
                        'icon' => 'fas fa-check-circle'
                    ]);
                    StatCard::render([
                        'value' => $stats['utilisateurs_uniques'],
                        'label' => 'Utilisateurs Uniques',
                        'icon' => 'fas fa-users'
                    ]);
                    ?>
                </div>

                <!-- Reservations -->
                <h3><i class="fas fa-calendar"></i> Réservations</h3>
                <?php if (empty($reservations)): ?>
                    <p style="text-align: center; padding: 2rem;">Aucune réservation</p>
                <?php else: ?>
                    <?php
                    Table::render([
                        'headers' => ['Membre', 'Début', 'Fin', 'Statut'],
                        'rows' => $reservations
                    ], function ($res) {
                        ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($res['membre_nom'] . ' ' . $res['membre_prenom']) ?>
                            </td>
                            <td>
                                <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?>
                            </td>
                            <td>
                                <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?>
                            </td>
                            <td>
                                <?php
                                Badge::render([
                                    'text' => ucfirst($res['statut']),
                                    'variant' => $res['statut'] === 'active' ? 'success' : 'default',
                                    'size' => 'small'
                                ]);
                                ?>
                            </td>
                        </tr>
                        <?php
                    });
                    ?>
                <?php endif; ?>
            </div>
        </main>

        <?php
        $this->renderFooter();
    }

    // Garder renderForm() tel quel...

    private function getEtatBadgeVariant($etat)
    {
        return match ($etat) {
            'libre' => 'success',
            'reserve' => 'warning',
            'en_maintenance' => 'danger',
            default => 'default'
        };
    }

    private function getEtatColor($etat)
    {
        return match ($etat) {
            'libre' => '#10b981',
            'reserve' => '#f59e0b',
            'en_maintenance' => '#ef4444',
            default => '#6b7280'
        };
    }
}
?>