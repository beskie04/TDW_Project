<?php
/**
 * Filter Component (Generic Framework Component)
 * Reusable dropdown filter with icon and label
 * 
 * @param array $options Configuration:
 *   - 'id' (string): HTML ID attribute
 *   - 'label' (string): Filter label text
 *   - 'icon' (string|null): FontAwesome icon class
 *   - 'options' (array): Filter options [['value' => '', 'text' => ''], ...]
 *   - 'placeholder' (string): Default option text (default: 'All')
 *   - 'cssClass' (string): Additional CSS classes
 *   - 'onChange' (string|null): JavaScript onChange handler
 */

class Filter
{
    public static function render($options = [])
    {
        $defaults = [
            'id' => 'filter-' . uniqid(),
            'label' => 'Filter',
            'icon' => null,
            'options' => [],
            'placeholder' => 'All',
            'cssClass' => '',
            'onChange' => null
        ];

        $options = array_merge($defaults, $options);
        ?>
        <div class="filter-group <?= htmlspecialchars($options['cssClass']) ?>">
            <label for="<?= htmlspecialchars($options['id']) ?>">
                <?php if ($options['icon']): ?>
                    <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
                <?php endif; ?>
                <?= htmlspecialchars($options['label']) ?>
            </label>

            <select id="<?= htmlspecialchars($options['id']) ?>" class="filter-select" <?= $options['onChange'] ? 'onchange="' . htmlspecialchars($options['onChange']) . '"' : '' ?>>
                <option value=""><?= htmlspecialchars($options['placeholder']) ?></option>
                <?php foreach ($options['options'] as $opt): ?>
                    <option value="<?= htmlspecialchars($opt['value']) ?>">
                        <?= htmlspecialchars($opt['text']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }
}
?>