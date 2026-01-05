<?php
/**
 * SearchInput Component (Generic Framework Component)
 * Reusable search input field with icon
 * 
 * @param array $options Configuration:
 *   - 'id' (string): HTML ID attribute
 *   - 'label' (string|null): Input label text
 *   - 'icon' (string): Icon class (default: 'fas fa-search')
 *   - 'placeholder' (string): Placeholder text (default: 'Search...')
 *   - 'cssClass' (string): Additional CSS classes
 *   - 'onInput' (string|null): JavaScript onInput handler
 */

class SearchInput
{
    public static function render($options = [])
    {
        $defaults = [
            'id' => 'search-input-' . uniqid(),
            'label' => null,
            'icon' => 'fas fa-search',
            'placeholder' => 'Search...',
            'cssClass' => '',
            'onInput' => null
        ];

        $options = array_merge($defaults, $options);
        ?>
        <div class="filter-group search-group <?= htmlspecialchars($options['cssClass']) ?>">
            <?php if ($options['label']): ?>
                <label for="<?= htmlspecialchars($options['id']) ?>">
                    <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
                    <?= htmlspecialchars($options['label']) ?>
                </label>
            <?php endif; ?>

            <input type="text" id="<?= htmlspecialchars($options['id']) ?>" class="search-input"
                placeholder="<?= htmlspecialchars($options['placeholder']) ?>" <?= $options['onInput'] ? 'oninput="' . htmlspecialchars($options['onInput']) . '"' : '' ?>>
        </div>
        <?php
    }
}
?>