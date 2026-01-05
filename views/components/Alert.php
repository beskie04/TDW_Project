<?php
/**
 * Alert Component (Generic Framework Component)
 * Alert banner for notifications, messages, or highlighted content
 * 
 * @param array $options Configuration:
 *   - 'title' (string|null): Alert title
 *   - 'icon' (string|null): FontAwesome icon class
 *   - 'variant' (string): Alert variant ('info', 'success', 'warning', 'danger', 'default')
 *   - 'dismissible' (bool): Show close button (default: false)
 *   - 'cssClass' (string): Additional CSS classes
 * @param callable $content Callback that renders alert content
 */

class Alert
{
    public static function render($options = [], $content = null)
    {
        $defaults = [
            'title' => null,
            'icon' => null,
            'variant' => 'default',
            'dismissible' => false,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        $variantClass = 'alert-' . $options['variant'];
        ?>
        <div class="alert <?= $variantClass ?> <?= htmlspecialchars($options['cssClass']) ?>">
            <?php if ($options['title'] || $options['icon']): ?>
                <div class="alert-header">
                    <?php if ($options['icon']): ?>
                        <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
                    <?php endif; ?>
                    <?php if ($options['title']): ?>
                        <h3><?= htmlspecialchars($options['title']) ?></h3>
                    <?php endif; ?>
                    <?php if ($options['dismissible']): ?>
                        <button class="alert-close" onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="alert-content">
                <?php if ($content && is_callable($content))
                    $content(); ?>
            </div>
        </div>
        <?php
    }
}
?>