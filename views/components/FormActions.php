<?php
/**
 * FormActions Component (Generic Framework Component)
 * Container for form action buttons
 * 
 * @param array $options Configuration:
 *   - 'align' (string): Alignment ('left', 'center', 'right', 'space-between') (default: 'left')
 *   - 'cssClass' (string): Additional CSS classes
 * @param callable $content Callback that renders buttons
 */
class FormActions
{
    public static function render($options = [], $content = null)
    {
        $defaults = [
            'align' => 'left',
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        $justifyMap = [
            'left' => 'flex-start',
            'center' => 'center',
            'right' => 'flex-end',
            'space-between' => 'space-between'
        ];

        $justify = $justifyMap[$options['align']] ?? 'flex-start';
        ?>
        <div class="form-actions <?= htmlspecialchars($options['cssClass']) ?>" style="justify-content: <?= $justify ?>;">
            <?php if ($content && is_callable($content))
                $content(); ?>
        </div>
        <?php
    }
}
?>