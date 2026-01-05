<?php
/**
 * InfoList Component (Generic Framework Component)
 * Key-value information list
 * 
 * @param array $items List items:
 *   Each item: ['label' => '', 'value' => '', 'icon' => ''] (icon optional)
 * @param array $options Configuration:
 *   - 'title' (string|null): Optional list title
 *   - 'titleIcon' (string|null): Icon for title
 *   - 'cssClass' (string): Additional CSS classes
 */

class InfoList
{
    public static function render($items, $options = [])
    {
        if (empty($items))
            return;

        $defaults = [
            'title' => null,
            'titleIcon' => null,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);
        ?>
                <div class="info-card <?= htmlspecialchars($options['cssClass']) ?>">
                    <?php if ($options['title']): ?>
                            <h3>
                                <?php if ($options['titleIcon']): ?>
                                        <i class="<?= htmlspecialchars($options['titleIcon']) ?>"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($options['title']) ?>
                            </h3>
                    <?php endif; ?>
            
                    <ul class="info-list">
                        <?php foreach ($items as $item): ?>
                                <li>
                                    <?php if (!empty($item['icon'])): ?>
                                            <i class="<?= htmlspecialchars($item['icon']) ?>"></i>
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($item['label']) ?>:</strong>
                                    <span><?= htmlspecialchars($item['value']) ?></span>
                                </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php
    }
}
?>