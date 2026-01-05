<?php
/**
 * List Component (Generic Framework Component)
 * Vertical list container for items
 * 
 * @param array $options Configuration:
 *   - 'gap' (string): Gap between items (default: '1.5rem')
 *   - 'cssClass' (string): Additional CSS classes
 * @param callable $content Callback that renders list items
 */

class ListContainer
{
    public static function render($options = [], $content = null)
    {
        $defaults = [
            'gap' => '1.5rem',
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        $style = sprintf('display: flex; flex-direction: column; gap: %s;', $options['gap']);
        ?>
        <div class="list-container <?= htmlspecialchars($options['cssClass']) ?>" style="<?= $style ?>">
            <?php if ($content && is_callable($content))
                $content(); ?>
        </div>
        <?php
    }
}
?>