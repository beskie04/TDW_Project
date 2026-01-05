<?php
/**
 * Generic Chart Component
 * Renders pure CSS/HTML charts without external libraries
 */
class Chart
{
    /**
     * Render a bar chart
     * 
     * @param array $options Configuration options
     *   - data: array of ['label' => string, 'value' => number, 'color' => string (optional)]
     *   - title: Chart title (optional)
     *   - height: Chart height in pixels (default: 300)
     *   - showValues: Show values on bars (default: true)
     *   - orientation: 'vertical' or 'horizontal' (default: 'vertical')
     */
    public static function renderBar($options = [])
    {
        $data = $options['data'] ?? [];
        $title = $options['title'] ?? '';
        $height = $options['height'] ?? 300;
        $showValues = $options['showValues'] ?? true;
        $orientation = $options['orientation'] ?? 'vertical';

        if (empty($data)) {
            echo '<p style="text-align: center; color: var(--gray-500);">Aucune donnée disponible</p>';
            return;
        }

        // Find max value for scaling
        $maxValue = max(array_column($data, 'value'));
        $maxValue = $maxValue > 0 ? $maxValue : 1;

        $colors = [
            'var(--primary-color)',
            'var(--accent-color)',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#8b5cf6',
            '#ec4899',
            '#06b6d4'
        ];

        ?>
        <div class="chart-container"
            style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <?php if ($title): ?>
                <h3 style="margin: 0 0 1.5rem 0; color: var(--dark-color); font-size: 1.1rem;">
                    <?= htmlspecialchars($title) ?>
                </h3>
            <?php endif; ?>

            <?php if ($orientation === 'vertical'): ?>
                <!-- Vertical Bar Chart -->
                <div
                    style="display: flex; align-items: flex-end; justify-content: space-around; height: <?= $height ?>px; gap: 1rem; border-bottom: 2px solid var(--gray-300); padding: 0 1rem;">
                    <?php foreach ($data as $index => $item): ?>
                        <?php
                        $percentage = ($item['value'] / $maxValue) * 100;
                        $color = $item['color'] ?? $colors[$index % count($colors)];
                        ?>
                        <div
                            style="display: flex; flex-direction: column; align-items: center; flex: 1; min-width: 60px; max-width: 120px;">
                            <div
                                style="width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%;">
                                <?php if ($showValues): ?>
                                    <div style="margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-color); font-size: 1rem;">
                                        <?= $item['value'] ?>
                                    </div>
                                <?php endif; ?>
                                <div style="width: 100%; height: <?= $percentage ?>%; background: <?= htmlspecialchars($color) ?>; border-radius: 8px 8px 0 0; transition: all 0.3s ease; position: relative;"
                                    title="<?= htmlspecialchars($item['label']) ?>: <?= $item['value'] ?>">
                                </div>
                            </div>
                            <div
                                style="margin-top: 0.75rem; text-align: center; font-size: 0.85rem; color: var(--gray-700); font-weight: 500; word-wrap: break-word; max-width: 100%;">
                                <?= htmlspecialchars($item['label']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Horizontal Bar Chart -->
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($data as $index => $item): ?>
                        <?php
                        $percentage = ($item['value'] / $maxValue) * 100;
                        $color = $item['color'] ?? $colors[$index % count($colors)];
                        ?>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="min-width: 150px; font-size: 0.9rem; color: var(--gray-700); font-weight: 500;">
                                <?= htmlspecialchars($item['label']) ?>
                            </div>
                            <div
                                style="flex: 1; height: 40px; background: var(--gray-100); border-radius: 8px; overflow: hidden; position: relative;">
                                <div
                                    style="height: 100%; width: <?= $percentage ?>%; background: <?= htmlspecialchars($color) ?>; border-radius: 8px; transition: width 0.5s ease; display: flex; align-items: center; justify-content: flex-end; padding-right: 0.75rem;">
                                    <?php if ($showValues): ?>
                                        <span style="color: white; font-weight: 600; font-size: 0.9rem;">
                                            <?= $item['value'] ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render a pie/donut chart using CSS conic gradient
     * 
     * @param array $options Configuration options
     *   - data: array of ['label' => string, 'value' => number, 'color' => string (optional)]
     *   - title: Chart title (optional)
     *   - size: Chart diameter in pixels (default: 250)
     *   - donut: Show as donut chart (default: false)
     *   - showLegend: Show legend (default: true)
     */
    public static function renderPie($options = [])
    {
        $data = $options['data'] ?? [];
        $title = $options['title'] ?? '';
        $size = $options['size'] ?? 250;
        $donut = $options['donut'] ?? false;
        $showLegend = $options['showLegend'] ?? true;

        if (empty($data)) {
            echo '<p style="text-align: center; color: var(--gray-500);">Aucune donnée disponible</p>';
            return;
        }

        $total = array_sum(array_column($data, 'value'));
        if ($total <= 0) {
            echo '<p style="text-align: center; color: var(--gray-500);">Aucune donnée disponible</p>';
            return;
        }

        $colors = [
            'var(--primary-color)',
            'var(--accent-color)',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#8b5cf6',
            '#ec4899',
            '#06b6d4'
        ];

        // Build conic gradient
        $gradientStops = [];
        $currentAngle = 0;

        foreach ($data as $index => $item) {
            $color = $item['color'] ?? $colors[$index % count($colors)];
            $percentage = ($item['value'] / $total) * 100;
            $angle = ($percentage / 100) * 360;

            $gradientStops[] = "$color {$currentAngle}deg " . ($currentAngle + $angle) . "deg";
            $currentAngle += $angle;

            $data[$index]['color'] = $color;
            $data[$index]['percentage'] = $percentage;
        }

        $gradient = 'conic-gradient(' . implode(', ', $gradientStops) . ')';

        ?>
        <div class="chart-container"
            style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <?php if ($title): ?>
                <h3 style="margin: 0 0 1.5rem 0; color: var(--dark-color); font-size: 1.1rem; text-align: center;">
                    <?= htmlspecialchars($title) ?>
                </h3>
            <?php endif; ?>

            <div style="display: flex; align-items: center; justify-content: center; gap: 3rem; flex-wrap: wrap;">
                <!-- Pie Chart -->
                <div style="position: relative;">
                    <div
                        style="width: <?= $size ?>px; height: <?= $size ?>px; border-radius: 50%; background: <?= $gradient ?>; position: relative;">
                        <?php if ($donut): ?>
                            <div
                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 60%; height: 60%; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <div style="text-align: center;">
                                    <div style="font-size: 2rem; font-weight: 700; color: var(--dark-color);">
                                        <?= $total ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray-600); margin-top: 0.25rem;">
                                        Total
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Legend -->
                <?php if ($showLegend): ?>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php foreach ($data as $item): ?>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div
                                    style="width: 20px; height: 20px; border-radius: 4px; background: <?= htmlspecialchars($item['color']) ?>; flex-shrink: 0;">
                                </div>
                                <div style="font-size: 0.9rem; color: var(--gray-700);">
                                    <?= htmlspecialchars($item['label']) ?>
                                    <span style="font-weight: 600; color: var(--dark-color); margin-left: 0.5rem;">
                                        <?= $item['value'] ?> (<?= number_format($item['percentage'], 1) ?>%)
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
?>