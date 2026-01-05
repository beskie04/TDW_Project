<?php
/**
 * FormInput Component (Generic Framework Component)
 * Generic form input field
 * 
 * @param array $options Configuration:
 *   - 'type' (string): Input type (default: 'text')
 *   - 'name' (string): Input name
 *   - 'id' (string|null): Input ID
 *   - 'value' (string): Input value
 *   - 'placeholder' (string): Placeholder text
 *   - 'required' (bool): Required field
 *   - 'disabled' (bool): Disabled state
 *   - 'min' (string|null): Min value (for date/number inputs)
 *   - 'max' (string|null): Max value (for date/number inputs)
 *   - 'cssClass' (string): Additional CSS classes
 *   - 'attributes' (array): Additional HTML attributes
 */
class FormInput
{
    public static function render($options = [])
    {
        $defaults = [
            'type' => 'text',
            'name' => '',
            'id' => null,
            'value' => '',
            'placeholder' => '',
            'required' => false,
            'disabled' => false,
            'min' => null,
            'max' => null,
            'cssClass' => '',
            'attributes' => []
        ];

        $options = array_merge($defaults, $options);
        $id = $options['id'] ?? $options['name'];

        $attrs = [];
        $attrs[] = 'type="' . htmlspecialchars($options['type']) . '"';
        $attrs[] = 'name="' . htmlspecialchars($options['name']) . '"';
        $attrs[] = 'id="' . htmlspecialchars($id) . '"';
        $attrs[] = 'class="form-control ' . htmlspecialchars($options['cssClass']) . '"';

        if ($options['value'])
            $attrs[] = 'value="' . htmlspecialchars($options['value']) . '"';
        if ($options['placeholder'])
            $attrs[] = 'placeholder="' . htmlspecialchars($options['placeholder']) . '"';
        if ($options['required'])
            $attrs[] = 'required';
        if ($options['disabled'])
            $attrs[] = 'disabled';
        if ($options['min'])
            $attrs[] = 'min="' . htmlspecialchars($options['min']) . '"';
        if ($options['max'])
            $attrs[] = 'max="' . htmlspecialchars($options['max']) . '"';

        foreach ($options['attributes'] as $key => $value) {
            $attrs[] = htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }

        echo '<input ' . implode(' ', $attrs) . '>';
    }
}

?>