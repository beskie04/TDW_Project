<?php
/**
 * Button Component (Generic Framework Component)
 * Reusable button with variants and states
 * 
 * @param array $options Configuration:
 *   - 'text' (string): Button text
 *   - 'icon' (string|null): FontAwesome icon class
 *   - 'variant' (string): Button variant ('primary', 'secondary', 'danger', 'success', 'default')
 *   - 'size' (string): Size ('small', 'medium', 'large') (default: 'medium')
 *   - 'href' (string|null): If set, renders as <a> tag
 *   - 'type' (string): Button type for <button> tag (default: 'button')
 *   - 'onClick' (string|null): JavaScript onClick handler
 *   - 'disabled' (bool): Disabled state (default: false)
 *   - 'block' (bool): Full width button (default: false)
 *   - 'cssClass' (string): Additional CSS classes
 *   - 'attributes' (array): Additional HTML attributes
 */

class Button
{
    public static function render($options = [])
    {
        $defaults = [
            'text' => '',
            'icon' => null,
            'variant' => 'default',
            'size' => 'medium',
            'href' => null,
            'type' => 'button',
            'onClick' => null,
            'disabled' => false,
            'block' => false,
            'cssClass' => '',
            'attributes' => []
        ];

        $options = array_merge($defaults, $options);

        $classes = ['btn'];
        $classes[] = 'btn-' . $options['variant'];
        $classes[] = 'btn-' . $options['size'];
        if ($options['block'])
            $classes[] = 'btn-block';
        if ($options['cssClass'])
            $classes[] = $options['cssClass'];

        $classString = implode(' ', $classes);

        // Build attributes
        $attrs = [];
        if ($options['onClick'])
            $attrs[] = 'onclick="' . htmlspecialchars($options['onClick']) . '"';
        if ($options['disabled'])
            $attrs[] = 'disabled';

        foreach ($options['attributes'] as $key => $value) {
            $attrs[] = htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }

        $attrString = implode(' ', $attrs);

        // Render as link or button
        if ($options['href'] && !$options['disabled']) {
            ?>
            <a href="<?= htmlspecialchars($options['href']) ?>" class="<?= $classString ?>" <?= $attrString ?>>
                <?php if ($options['icon']): ?>
                    <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
                <?php endif; ?>
                <?= htmlspecialchars($options['text']) ?>
            </a>
            <?php
        } else {
            ?>
            <button type="<?= htmlspecialchars($options['type']) ?>" class="<?= $classString ?>" <?= $attrString ?>>
                <?php if ($options['icon']): ?>
                    <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
                <?php endif; ?>
                <?= htmlspecialchars($options['text']) ?>
            </button>
            <?php
        }
    }
}
?>