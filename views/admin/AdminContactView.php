<?php
require_once __DIR__ . '/../BaseView.php';

// Import Components
require_once __DIR__ . '/../components/Section.php';
require_once __DIR__ . '/../components/Card.php';
require_once __DIR__ . '/../components/Table.php';
require_once __DIR__ . '/../components/Badge.php';
require_once __DIR__ . '/../components/Filter.php';
require_once __DIR__ . '/../components/FilterBar.php';
require_once __DIR__ . '/../components/Modal.php';

class AdminContactView extends BaseView
{
    public function __construct()
    {
        $this->pageTitle = 'Gestion des Messages de Contact';
    }

    /**
     * Liste des messages
     */
    public function renderListe($messages, $stats)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-envelope"></i> Gestion des Messages de Contact</h1>
                    <p class="subtitle">Consultez et gérez les messages reçus via le formulaire de contact</p>
                </div>

                <!-- Statistics Cards -->
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <?php
                    $statsCards = [
                        ['label' => 'Total', 'value' => $stats['total'] ?? 0, 'icon' => 'fas fa-envelope', 'color' => '#3b82f6'],
                        ['label' => 'Nouveaux', 'value' => $stats['nouveau'] ?? 0, 'icon' => 'fas fa-star', 'color' => '#f59e0b'],
                        ['label' => 'Lus', 'value' => $stats['lu'] ?? 0, 'icon' => 'fas fa-check', 'color' => '#10b981'],
                        ['label' => 'Cette semaine', 'value' => $stats['cette_semaine'] ?? 0, 'icon' => 'fas fa-calendar-week', 'color' => '#8b5cf6']
                    ];

                    foreach ($statsCards as $stat) {
                        ?>
                        <div
                            style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem; font-weight: 500;">
                                        <?= $stat['label'] ?>
                                    </p>
                                    <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700; color: var(--dark-color);">
                                        <?= $stat['value'] ?>
                                    </h3>
                                </div>
                                <div style="width: 56px; height: 56px; background: <?= $stat['color'] ?>15; border-radius: 12px; 
                                            display: flex; align-items: center; justify-content: center;">
                                    <i class="<?= $stat['icon'] ?>" style="color: <?= $stat['color'] ?>; font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <!-- Filters -->
                <?php
                FilterBar::render([
                    'resetText' => 'Réinitialiser',
                    'resetId' => 'reset-filters'
                ], function () {
                    Filter::render([
                        'id' => 'filter-statut',
                        'label' => 'Statut',
                        'icon' => 'fas fa-filter',
                        'placeholder' => 'Tous les statuts',
                        'options' => [
                            ['value' => 'nouveau', 'text' => 'Nouveaux'],
                            ['value' => 'lu', 'text' => 'Lus'],
                            ['value' => 'archive', 'text' => 'Archivés']
                        ]
                    ]);
                    ?>
                    <div class="filter-group">
                        <label>
                            <i class="fas fa-search"></i> Recherche
                        </label>
                        <input type="text" id="search-input" class="filter-select" placeholder="Nom, email, sujet...">
                    </div>
                    <?php
                });
                ?>

                <!-- Messages Table -->
                <div id="messages-container">
                    <?php $this->renderMessagesTable($messages); ?>
                </div>
            </div>
        </main>

        <script>
            // Filtres dynamiques
            const statutFilter = document.getElementById('filter-statut');
            const searchInput = document.getElementById('search-input');
            const resetBtn = document.getElementById('reset-filters');

            function applyFilters() {
                const statut = statutFilter.value;
                const search = searchInput.value;

                const params = new URLSearchParams({
                    page: 'admin',
                    section: 'messages',
                    statut: statut,
                    search: search
                });

                window.location.href = '?' + params.toString();
            }

            if (statutFilter) {
                statutFilter.addEventListener('change', applyFilters);
            }

            if (searchInput) {
                let timeout;
                searchInput.addEventListener('input', function () {
                    clearTimeout(timeout);
                    timeout = setTimeout(applyFilters, 500);
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    window.location.href = '?page=admin&section=messages';
                });
            }

            // Actions sur les messages
            function deleteMessage(id) {
                if (confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
                    window.location.href = '?page=admin&section=messages&action=delete&id=' + id;
                }
            }
        </script>

        <?php
        $this->renderFooter();
    }

    /**
     * Afficher le tableau des messages
     */
    private function renderMessagesTable($messages)
    {
        if (empty($messages)) {
            ?>
            <div
                style="background: white; padding: 3rem; text-align: center; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <i class="fas fa-inbox" style="font-size: 4rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                <h3 style="color: var(--gray-600); margin: 0;">Aucun message trouvé</h3>
                <p style="color: var(--gray-500); margin: 0.5rem 0 0 0;">Essayez de modifier vos filtres</p>
            </div>
            <?php
            return;
        }

        Table::render([
            'headers' => ['Date', 'Expéditeur', 'Email', 'Sujet', 'Statut', 'Actions'],
            'rows' => $messages,
            'striped' => true,
            'hoverable' => true
        ], function ($message) {
            ?>
            <tr>
                <td style="white-space: nowrap;">
                    <?= date('d/m/Y H:i', strtotime($message['date_envoi'])) ?>
                </td>
                <td>
                    <?= htmlspecialchars($message['nom']) ?>
                </td>
                <td>
                    <?= htmlspecialchars($message['email']) ?>
                </td>
                <td>
                    <strong>
                        <?= htmlspecialchars(mb_substr($message['sujet'], 0, 50)) ?>
                    </strong>
                    <?= strlen($message['sujet']) > 50 ? '...' : '' ?>
                </td>
                <td>
                    <?php
                    $variants = [
                        'nouveau' => 'warning',
                        'lu' => 'success',
                        'archive' => 'default'
                    ];

                    Badge::render([
                        'text' => ucfirst($message['statut']),
                        'variant' => $variants[$message['statut']] ?? 'default',
                        'size' => 'small'
                    ]);
                    ?>
                </td>
                <td style="white-space: nowrap;">
                    <a href="?page=admin&section=messages&action=details&id=<?= $message['id_contact'] ?>"
                        class="btn btn-sm btn-primary" title="Voir les détails">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button onclick="deleteMessage(<?= $message['id_contact'] ?>)" class="btn btn-sm btn-danger" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php
        });
    }

    /**
     * Afficher les détails d'un message
     */
    public function renderDetails($message)
    {
        $this->renderHeader();
        ?>

        <main class="content-wrapper">
            <div class="container">
                <!-- Back button -->
                <a href="?page=admin&section=messages" class="btn btn-outline" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>

                <!-- Message Details -->
                <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                    <!-- Header -->
                    <div style="padding: 2rem; border-bottom: 1px solid var(--gray-200); background: #f7fafc;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div>
                                <h1 style="margin: 0 0 0.5rem 0; font-size: 1.75rem; color: var(--dark-color);">
                                    <?= htmlspecialchars($message['sujet']) ?>
                                </h1>
                                <?php
                                $variants = [
                                    'nouveau' => 'warning',
                                    'lu' => 'success',
                                    'archive' => 'default'
                                ];

                                Badge::render([
                                    'text' => ucfirst($message['statut']),
                                    'variant' => $variants[$message['statut']] ?? 'default',
                                    'size' => 'medium'
                                ]);
                                ?>
                            </div>
                            <button onclick="deleteMessage(<?= $message['id_contact'] ?>)" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>

                        <!-- Sender Info -->
                        <div
                            style="display: flex; gap: 2rem; padding: 1rem; background: white; border-radius: 8px; margin-top: 1rem;">
                            <div style="flex: 1;">
                                <p style="margin: 0 0 0.25rem 0; font-size: 0.875rem; color: var(--gray-600);">
                                    <i class="fas fa-user"></i> Expéditeur
                                </p>
                                <p style="margin: 0; font-weight: 600; color: var(--dark-color);">
                                    <?= htmlspecialchars($message['nom']) ?>
                                </p>
                            </div>
                            <div style="flex: 1;">
                                <p style="margin: 0 0 0.25rem 0; font-size: 0.875rem; color: var(--gray-600);">
                                    <i class="fas fa-envelope"></i> Email
                                </p>
                                <p style="margin: 0; font-weight: 600; color: var(--dark-color);">
                                    <a href="mailto:<?= htmlspecialchars($message['email']) ?>"
                                        style="color: var(--primary-color); text-decoration: none;">
                                        <?= htmlspecialchars($message['email']) ?>
                                    </a>
                                </p>
                            </div>
                            <div style="flex: 1;">
                                <p style="margin: 0 0 0.25rem 0; font-size: 0.875rem; color: var(--gray-600);">
                                    <i class="fas fa-calendar"></i> Date d'envoi
                                </p>
                                <p style="margin: 0; font-weight: 600; color: var(--dark-color);">
                                    <?= date('d/m/Y à H:i', strtotime($message['date_envoi'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div style="padding: 2rem;">
                        <h3 style="margin: 0 0 1rem 0; color: var(--dark-color); font-size: 1.125rem;">
                            <i class="fas fa-comment-alt"></i> Message
                        </h3>
                        <div
                            style="background: #f7fafc; padding: 1.5rem; border-radius: 8px; border-left: 4px solid var(--primary-color);">
                            <p style="margin: 0; line-height: 1.8; color: var(--gray-700); white-space: pre-wrap;">
                                <?= nl2br(htmlspecialchars($message['message'])) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="padding: 1.5rem 2rem; border-top: 1px solid var(--gray-200); background: #f7fafc; 
                                display: flex; gap: 1rem; justify-content: flex-end;">
                        <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: <?= urlencode($message['sujet']) ?>"
                            class="btn btn-primary">
                            <i class="fas fa-reply"></i> Répondre par email
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <script>
            function deleteMessage(id) {
                if (confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
                    window.location.href = '?page=admin&section=messages&action=delete&id=' + id;
                }
            }
        </script>

        <?php
        $this->renderFooter();
    }
}
?>