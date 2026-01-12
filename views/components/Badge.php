<?php
/**
 * Badge Component (Generic Framework Component)
 * Displays a colored badge/label
 */

class Badge
{
    public static function render($options = [])
    {
        $defaults = [
            'text' => '',
            'variant' => 'default', // 'primary', 'success', 'warning', 'danger', 'info', 'default', 'purple', 'pink'
            'size' => 'medium', // 'small', 'medium', 'large'
            'icon' => null,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        // Get color based on variant
        $colors = [
            'primary' => PRIMARY_COLOR,
            'success' => SUCCESS_COLOR,
            'warning' => WARNING_COLOR,
            'danger' => DANGER_COLOR,
            'info' => INFO_COLOR,
            'purple' => BADGE_PURPLE,
            'pink' => BADGE_PINK,
            'indigo' => BADGE_INDIGO,
            'teal' => BADGE_TEAL,
            'default' => BADGE_DEFAULT
        ];

        $bgColor = $colors[$options['variant']] ?? $colors['default'];

        // Size mapping
        $sizes = [
            'small' => ['padding' => '0.25rem 0.75rem', 'fontSize' => '0.75rem'],
            'medium' => ['padding' => '0.375rem 1rem', 'fontSize' => '0.875rem'],
            'large' => ['padding' => '0.5rem 1.25rem', 'fontSize' => '0.95rem']
        ];

        $sizeStyle = $sizes[$options['size']] ?? $sizes['medium'];
        ?>
        <span class="badge <?= htmlspecialchars($options['cssClass']) ?>"
              style="display: inline-flex; 
                     align-items: center; 
                     gap: <?= SPACING_SM ?>; 
                     background: <?= htmlspecialchars($bgColor) ?>; 
                     color: <?= TEXT_WHITE ?>; 
                     padding: <?= $sizeStyle['padding'] ?>; 
                     border-radius: <?= RADIUS_FULL ?>; 
                     font-size: <?= $sizeStyle['fontSize'] ?>; 
                     font-weight: 600;
                     white-space: nowrap;">
            <?php if ($options['icon']): ?>
                <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
            <?php endif; ?>
            <?= htmlspecialchars($options['text']) ?>
        </span>
        <?php
    }
}
?>