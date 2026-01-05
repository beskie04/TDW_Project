<?php
/**
 * Tag Component (Generic Framework Component)
 * Clickable tag/pill for categories, links, labels
 * 
 * @param array $options Configuration:
 *   - 'text' (string): Tag text
 *   - 'href' (string|null): Link URL (if null, renders as span)
 *   - 'icon' (string|null): Optional icon
 *   - 'variant' (string): Color variant ('primary', 'secondary', 'success', 'info', 'default')
 *   - 'size' (string): Size ('small', 'medium', 'large') (default: 'medium')
 *   - 'removable' (bool): Show remove button (default: false)
 *   - 'onRemove' (string|null): JavaScript onClick for remove button
 *   - 'cssClass' (string): Additional CSS classes
 */

class Tag
{
    public static function render($options = [])
    {
        $defaults = [
            'text' => '',
            'href' => null,
            'icon' => null,
            'variant' => 'default',
            'size' => 'medium',
            'removable' => false,
            'onRemove' => null,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        $classes = ['tag'];
        $classes[] = 'tag-' . $options['variant'];
        $classes[] = 'tag-' . $options['size'];
        if ($options['cssClass'])
            $classes[] = $options['cssClass'];

        $classString = implode(' ', $classes);

        // Render as link or span
        $tagName = $options['href'] ? 'a' : 'span';
        $hrefAttr = $options['href'] ? 'href="' . htmlspecialchars($options['href']) . '"' : '';
        ?>
        <<?= $tagName ?>         <?= $hrefAttr ?> class="<?= $classString ?>">
            <?php if ($options['icon']): ?>
                <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
            <?php endif; ?>
            <?= htmlspecialchars($options['text']) ?>
            <?php if ($options['removable']): ?>
                <button type="button" class="tag-remove" <?= $options['onRemove'] ? 'onclick="' . htmlspecialchars($options['onRemove']) . '"' : '' ?>>
                    <i class="fas fa-times"></i>
                </button>
            <?php endif; ?>
        </<?= $tagName ?>>
        <?php
    }
}
?>