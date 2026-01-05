<?php
/**
 * Badge Component (Generic Framework Component)
 * Reusable badge for status, tags, labels
 * 
 * @param array $options Configuration:
 *   - 'text' (string): Badge text
 *   - 'color' (string|null): Background color (CSS value)
 *   - 'variant' (string): Predefined variant ('primary', 'success', 'warning', 'danger', 'info', 'default')
 *   - 'size' (string): Size ('small', 'medium', 'large') (default: 'medium')
 *   - 'icon' (string|null): Optional icon
 *   - 'cssClass' (string): Additional CSS classes
 */

class Badge
{
    public static function render($options = [])
    {
        $defaults = [
            'text' => '',
            'color' => null,
            'variant' => 'default',
            'size' => 'medium',
            'icon' => null,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        $sizeClass = 'badge-' . $options['size'];
        $variantClass = 'badge-' . $options['variant'];
        $style = $options['color'] ? 'background: ' . htmlspecialchars($options['color']) . ';' : '';
        ?>
        <span class="badge <?= $sizeClass ?> <?= $variantClass ?> <?= htmlspecialchars($options['cssClass']) ?>" <?= $style ? 'style="' . $style . '"' : '' ?>>
            <?php if ($options['icon']): ?>
                <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
            <?php endif; ?>
            <?= htmlspecialchars($options['text']) ?>
        </span>
        <?php
    }
}
?>