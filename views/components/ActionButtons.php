<?php
/**
 * ActionButtons Component (Generic Framework Component)
 * Action buttons for table rows (view, edit, delete, etc.)
 * 
 * @param array $actions Array of actions:
 *   Each action: [
 *     'type' => 'view|edit|delete|custom',
 *     'href' => 'url' (for view/edit),
 *     'onClick' => 'js function' (for delete/custom),
 *     'icon' => 'fas fa-icon',
 *     'title' => 'Tooltip text',
 *     'target' => '_blank' (optional)
 *   ]
 * @param array $options Configuration:
 *   - 'cssClass' (string): Additional CSS classes
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