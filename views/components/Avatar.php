<?php
/**
 * Avatar Component (Generic Framework Component)
 * User photo/avatar display
 * 
 * @param array $options Configuration:
 *   - 'src' (string|null): Image source URL
 *   - 'alt' (string): Alt text (default: 'Avatar')
 *   - 'size' (string): Size ('small', 'medium', 'large', 'xlarge') (default: 'medium')
 *   - 'icon' (string): Fallback icon (default: 'fas fa-user')
 *   - 'shape' (string): 'circle' or 'square' (default: 'circle')
 *   - 'cssClass' (string): Additional CSS classes
 */

class Avatar
{
    public static function render($options = [])
    {
        $defaults = [
            'src' => null,
            'alt' => 'Avatar',
            'size' => 'medium',
            'icon' => 'fas fa-user',
            'shape' => 'circle',
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        $sizeClass = 'avatar-' . $options['size'];
        $shapeClass = 'avatar-' . $options['shape'];
        ?>
        <div class="avatar <?= $sizeClass ?> <?= $shapeClass ?> <?= htmlspecialchars($options['cssClass']) ?>">
            <?php if ($options['src']): ?>
                <img src="<?= htmlspecialchars($options['src']) ?>" alt="<?= htmlspecialchars($options['alt']) ?>">
            <?php else: ?>
                <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>