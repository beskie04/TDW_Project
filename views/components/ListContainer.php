<?php
/**
 * ListContainer Component (Generic Framework Component)
 * Container for list items with consistent spacing
 */

class ListContainer
{
    public static function render($options = [], $callback = null)
    {
        $defaults = [
            'gap' => SPACING_LG,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);
        ?>
        <div class="list-container <?= htmlspecialchars($options['cssClass']) ?>"
             style="display: flex; 
                    flex-direction: column; 
                    gap: <?= htmlspecialchars($options['gap']) ?>;">
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