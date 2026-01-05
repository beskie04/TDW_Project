<?php
/**
 * Breadcrumb Component (Generic Framework Component)
 * Navigation breadcrumb trail
 * 
 * @param array $items Breadcrumb items:
 *   Each item: ['text' => '', 'url' => ''] or ['text' => ''] for current page
 * @param array $options Configuration:
 *   - 'separator' (string): Separator icon (default: 'fas fa-chevron-right')
 *   - 'cssClass' (string): Additional CSS classes
 */

class Breadcrumb
{
    public static function render($items, $options = [])
    {
        if (empty($items))
            return;

        $defaults = [
            'separator' => 'fas fa-chevron-right',
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);
        ?>
        <nav class="breadcrumb <?= htmlspecialchars($options['cssClass']) ?>" aria-label="breadcrumb">
            <?php foreach ($items as $index => $item): ?>
                <?php if ($index > 0): ?>
                    <i class="<?= htmlspecialchars($options['separator']) ?>"></i>
                <?php endif; ?>

                <?php if (!empty($item['url'])): ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>">
                        <?= htmlspecialchars($item['text']) ?>
                    </a>
                <?php else: ?>
                    <span><?= htmlspecialchars($item['text']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <?php
    }
}
?>