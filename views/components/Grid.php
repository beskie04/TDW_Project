<?php
/**
 * Grid Component (Generic Framework Component)
 * Responsive grid layout system
 * 
 * @param array $options Configuration:
 *   - 'columns' (int): Number of columns (default: 3)
 *   - 'minWidth' (string): Min width per item (default: '300px')
 *   - 'gap' (string): Gap between items (default: '2rem')
 *   - 'cssClass' (string): Additional CSS classes
 * @param callable $content Callback that renders grid items
 */

class Grid
{
    public static function render($options = [], $content = null)
    {
        $defaults = [
            'columns' => 3,
            'minWidth' => '300px',
            'gap' => '2rem',
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        $gridStyle = sprintf(
            'display: grid; grid-template-columns: repeat(auto-fill, minmax(%s, 1fr)); gap: %s;',
            $options['minWidth'],
            $options['gap']
        );
        ?>
        <div class="grid <?= htmlspecialchars($options['cssClass']) ?>" style="<?= $gridStyle ?>">
            <?php if ($content && is_callable($content))
                $content(); ?>
        </div>
        <?php
    }
}
?>