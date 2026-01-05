<?php
/**
 * StatCard Component (Generic Framework Component)
 * Statistics card with icon, number, and label
 * 
 * @param array $options Configuration:
 *   - 'value' (string|int): Main statistic value
 *   - 'label' (string): Description label
 *   - 'icon' (string): FontAwesome icon class
 *   - 'color' (string): Icon background color (CSS value)
 *   - 'trend' (array|null): Optional trend ['value' => '+12%', 'direction' => 'up', 'color' => 'green']
 *   - 'cssClass' (string): Additional CSS classes
 */

class StatCard
{
    public static function render($options = [])
    {
        $defaults = [
            'value' => '0',
            'label' => '',
            'icon' => 'fas fa-chart-line',
            'color' => 'var(--primary-color, #2563eb)',
            'trend' => null,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);
        ?>
        <div class="stat-card <?= htmlspecialchars($options['cssClass']) ?>">
            <div class="stat-icon" style="background: <?= htmlspecialchars($options['color']) ?>;">
                <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
            </div>

            <div class="stat-info">
                <h3 class="stat-value"><?= htmlspecialchars($options['value']) ?></h3>
                <p class="stat-label"><?= htmlspecialchars($options['label']) ?></p>

                <?php if ($options['trend']): ?>
                    <span class="stat-trend stat-trend-<?= htmlspecialchars($options['trend']['direction']) ?>"
                        style="color: <?= htmlspecialchars($options['trend']['color']) ?>;">
                        <i class="fas fa-arrow-<?= $options['trend']['direction'] === 'up' ? 'up' : 'down' ?>"></i>
                        <?= htmlspecialchars($options['trend']['value']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
?>