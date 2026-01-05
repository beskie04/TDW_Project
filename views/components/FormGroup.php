<?php
/**
 * FormGroup Component (Generic Framework Component)
 * Wrapper for form inputs with label
 * 
 * @param array $options Configuration:
 *   - 'label' (string): Input label
 *   - 'required' (bool): Required field (default: false)
 *   - 'cssClass' (string): Additional CSS classes
 * @param callable $content Callback that renders input element
 */
class FormGroup
{
    public static function render($options = [], $content = null)
    {
        $defaults = [
            'label' => '',
            'required' => false,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);
        ?>
        <div class="form-group <?= htmlspecialchars($options['cssClass']) ?>">
            <label>
                <?= htmlspecialchars($options['label']) ?>
                <?php if ($options['required']): ?>
                    <span class="required">*</span>
                <?php endif; ?>
            </label>
            <?php if ($content && is_callable($content))
                $content(); ?>
        </div>
        <?php
    }
}



?>