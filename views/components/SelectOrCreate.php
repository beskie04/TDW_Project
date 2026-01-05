<?php
/**
 * SelectOrCreate Component (Generic Framework Component)
 * Select dropdown with ability to create new items inline
 * 
 * @param array $options Configuration:
 *   - 'id' (string): HTML ID
 *   - 'name' (string): Input name
 *   - 'label' (string): Field label
 *   - 'items' (array): Existing items [['value' => '', 'text' => ''], ...]
 *   - 'value' (string): Selected value
 *   - 'required' (bool): Required field
 *   - 'createLabel' (string): Label for "create new" option (default: '+ Créer nouveau')
 *   - 'placeholder' (string): Placeholder text
 *   - 'newItemPlaceholder' (string): Placeholder for new item input
 */

class SelectOrCreate
{
    public static function render($options = [])
    {
        $defaults = [
            'id' => 'select-' . uniqid(),
            'name' => '',
            'label' => '',
            'items' => [],
            'value' => '',
            'required' => false,
            'createLabel' => '+ Créer nouveau',
            'placeholder' => 'Sélectionnez une option',
            'newItemPlaceholder' => 'Nom du nouvel élément'
        ];

        $options = array_merge($defaults, $options);
        
        $selectId = $options['id'];
        $inputId = $selectId . '_new';
        ?>
        <div class="select-or-create-wrapper">
            <label>
                <?= htmlspecialchars($options['label']) ?>
                <?php if ($options['required']): ?>
                    <span class="required">*</span>
                <?php endif; ?>
            </label>

            <!-- Dropdown Select -->
            <select id="<?= $selectId ?>" 
                    name="<?= $options['name'] ?>" 
                    class="form-control select-or-create"
                    <?= $options['required'] ? 'required' : '' ?>
                    onchange="toggleNewInput(this, '<?= $inputId ?>')">
                
                <option value=""><?= htmlspecialchars($options['placeholder']) ?></option>
                
                <?php foreach ($options['items'] as $item): ?>
                    <option value="<?= htmlspecialchars($item['value']) ?>" 
                            <?= $options['value'] == $item['value'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($item['text']) ?>
                    </option>
                <?php endforeach; ?>
                
                <option value="__CREATE_NEW__" style="color: var(--primary-color); font-weight: 600;">
                    <?= htmlspecialchars($options['createLabel']) ?>
                </option>
            </select>

            <!-- New Item Input (hidden by default) -->
            <div id="<?= $inputId ?>_container" class="new-item-container" style="display: none; margin-top: 0.5rem;">
                <input type="text" 
                       id="<?= $inputId ?>" 
                       name="<?= $options['name'] ?>_new"
                       class="form-control"
                       placeholder="<?= htmlspecialchars($options['newItemPlaceholder']) ?>"
                       disabled>
                <small class="form-text" style="color: var(--success-color);">
                    <i class="fas fa-info-circle"></i> 
                    Saisissez le nom et il sera créé automatiquement
                </small>
            </div>
        </div>

        <script>
        function toggleNewInput(selectElement, inputId) {
            const container = document.getElementById(inputId + '_container');
            const input = document.getElementById(inputId);
            
            if (selectElement.value === '__CREATE_NEW__') {
                // Show input for new item
                container.style.display = 'block';
                input.disabled = false;
                input.required = <?= $options['required'] ? 'true' : 'false' ?>;
                input.focus();
                
                // Disable select's required if input becomes required
                selectElement.required = false;
            } else {
                // Hide input
                container.style.display = 'none';
                input.disabled = true;
                input.required = false;
                input.value = '';
                
                // Restore select's required
                selectElement.required = <?= $options['required'] ? 'true' : 'false' ?>;
            }
        }
        </script>

        <style>
        .select-or-create-wrapper {
            margin-bottom: 1.5rem;
        }

        .new-item-container {
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .select-or-create option[value="__CREATE_NEW__"] {
            border-top: 2px solid var(--gray-300);
            margin-top: 0.5rem;
        }
        </style>
        <?php
    }
}
?>