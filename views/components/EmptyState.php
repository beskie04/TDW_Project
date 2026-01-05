<?php
/**
 * EmptyState Component (Generic Framework Component)
 * Display when no data/results found
 * 
 * @param array $options Configuration:
 *   - 'icon' (string): Icon to display (default: 'fas fa-inbox')
 *   - 'title' (string): Main message (default: 'No Results Found')
 *   - 'description' (string|null): Optional description
 *   - 'action' (array|null): Optional action button ['url' => '', 'text' => '', 'icon' => '']
 *   - 'cssClass' (string): Additional CSS classes
 */

class EmptyState
{
    public static function render($options = [])
    {
        $defaults = [
            'icon' => 'fas fa-inbox',
            'title' => 'No Results Found',
            'description' => null,
            'action' => null,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);
        ?>
        <div class="empty-state <?= htmlspecialchars($options['cssClass']) ?>">
            <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
            <h3><?= htmlspecialchars($options['title']) ?></h3>

            <?php if ($options['description']): ?>
                <p><?= htmlspecialchars($options['description']) ?></p>
            <?php endif; ?>

            <?php if ($options['action']): ?>
                <a href="<?= htmlspecialchars($options['action']['url']) ?>" class="btn btn-primary">
                    <?php if (!empty($options['action']['icon'])): ?>
                        <i class="<?= htmlspecialchars($options['action']['icon']) ?>"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($options['action']['text']) ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>