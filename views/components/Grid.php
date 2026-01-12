<?php
/**
 * Grid Component (Generic Framework Component)
 * Auto-responsive grid layout
 */

class Grid
{
    public static function render($options = [], $callback = null)
    {
        $defaults = [
            'columns' => null,        // Fixed columns (e.g., 3)
            'minWidth' => '300px',    // Min width for auto-fit
            'gap' => '1.5rem',        // Gap between items
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        $gridStyle = $options['columns'] 
            ? "grid-template-columns: repeat({$options['columns']}, 1fr);" 
            : "grid-template-columns: repeat(auto-fill, minmax({$options['minWidth']}, 1fr));";
        ?>
        <div class="grid-container <?= htmlspecialchars($options['cssClass']) ?>"
             style="display: grid; 
                    <?= $gridStyle ?> 
                    gap: <?= htmlspecialchars($options['gap']) ?>; 
                    align-items: stretch;">
            <?php
            if (is_callable($callback)) {
                $callback();
            }
            ?>
        </div>
        <?php
    }
}
?>