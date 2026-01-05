<?php
/**
 * FilterBar Component (Generic Framework Component)
 * Container for multiple filters with optional reset button
 * 
 * @param array $options Configuration:
 *   - 'showReset' (bool): Show reset button (default: true)
 *   - 'resetText' (string): Reset button text (default: 'Reset')
 *   - 'resetIcon' (string): Reset button icon (default: 'fas fa-redo')
 *   - 'resetId' (string): Reset button ID
 *   - 'cssClass' (string): Additional CSS classes
 * @param callable $content Callback that renders filter items
 */

class FilterBar
{
    public static function render($options = [], $content = null)
    {
        $defaults = [
            'showReset' => true,
            'resetText' => 'Reset',
            'resetIcon' => 'fas fa-redo',
            'resetId' => 'reset-filters',
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);
        ?>
        <div class="filters-section <?= htmlspecialchars($options['cssClass']) ?>">
            <div class="filters-wrapper">
                <?php if ($content && is_callable($content))
                    $content(); ?>

                <?php if ($options['showReset']): ?>
                    <button id="<?= htmlspecialchars($options['resetId']) ?>" class="btn-reset">
                        <i class="<?= htmlspecialchars($options['resetIcon']) ?>"></i>
                        <?= htmlspecialchars($options['resetText']) ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
?>