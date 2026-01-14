<?php
/**
 * ActionButtons Component (Generic Framework Component)
 * Action buttons for table rows (view, edit, delete, etc.)
 * MINIMAL DESIGN VERSION
 */

class ActionButtons
{
    public static function render($actions, $options = [])
    {
        $defaults = [
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);
        ?>
        <div class="action-buttons <?= htmlspecialchars($options['cssClass']) ?>">
            <?php foreach ($actions as $action): ?>
                <?php
                $type = $action['type'] ?? 'custom';
                $btnClass = 'btn-action btn-' . $type;
                $icon = $action['icon'] ?? self::getDefaultIcon($type);
                $title = $action['title'] ?? ucfirst($type);
                ?>

                <?php if (!empty($action['href'])): ?>
                    <a href="<?= htmlspecialchars($action['href']) ?>" class="<?= $btnClass ?>" title="<?= htmlspecialchars($title) ?>"
                        <?= !empty($action['target']) ? 'target="' . htmlspecialchars($action['target']) . '"' : '' ?>>
                        <i class="<?= htmlspecialchars($icon) ?>"></i>
                    </a>
                <?php elseif (!empty($action['onClick'])): ?>
                    <button type="button" class="<?= $btnClass ?>" title="<?= htmlspecialchars($title) ?>"
                        onclick="<?= htmlspecialchars($action['onClick']) ?>">
                        <i class="<?= htmlspecialchars($icon) ?>"></i>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <style>
            /* ============================================
               MINIMAL ACTION BUTTONS DESIGN
               ============================================ */
            .action-buttons {
                display: flex;
                gap: 0.5rem;
                justify-content: flex-start;
                align-items: center;
            }

            .btn-action {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 6px;
                border: 1px solid #e5e7eb;
                background: #ffffff;
                color: #6b7280;
                cursor: pointer;
                transition: all 0.2s ease;
                text-decoration: none;
                font-size: 14px;
            }

            .btn-action:hover {
                background: #f9fafb;
                border-color: #d1d5db;
                color: #374151;
                transform: translateY(-1px);
            }

            .btn-action:active {
                transform: translateY(0);
            }

            /* Optional: Add subtle color on hover for context */
            .btn-action.btn-view:hover {
                background: #f0f9ff;
                border-color: #bae6fd;
                color: #0284c7;
            }

            .btn-action.btn-edit:hover {
                background: #f0f9ff;
                border-color: #bae6fd;
                color: #0284c7;
            }

            .btn-action.btn-delete:hover {
                background: #fef2f2;
                border-color: #fecaca;
                color: #dc2626;
            }

            /* Alternative: If you want EVERYTHING the same (ultra minimal) */
            /*
            .btn-action:hover {
                background: #f3f4f6;
                border-color: #d1d5db;
                color: #111827;
            }
            */
        </style>
        <?php
    }

    private static function getDefaultIcon($type)
    {
        $icons = [
            'view' => 'fas fa-eye',
            'edit' => 'fas fa-edit',
            'delete' => 'fas fa-trash',
            'download' => 'fas fa-download',
            'print' => 'fas fa-print',
            'copy' => 'fas fa-copy',
            'custom' => 'fas fa-ellipsis-v'
        ];

        return $icons[$type] ?? 'fas fa-ellipsis-v';
    }
}
?>