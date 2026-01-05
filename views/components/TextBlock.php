<?php
/**
 * TextBlock Component (Generic Framework Component)
 * Flexible text content block with optional icon and styling
 * 
 * @param array $options Configuration:
 *   - 'title' (string|null): Block title
 *   - 'icon' (string|null): FontAwesome icon
 *   - 'content' (string|array): Text content (string or array of paragraphs)
 *   - 'cssClass' (string): Additional CSS classes (default: 'text-block')
 */

class TextBlock
{
    public static function render($options = [])
    {
        $defaults = [
            'title' => null,
            'icon' => null,
            'content' => '',
            'cssClass' => 'text-block'
        ];

        $options = array_merge($defaults, $options);
        ?>
        <div class="<?= htmlspecialchars($options['cssClass']) ?>">
            <?php if ($options['title']): ?>
                <h2>
                    <?php if ($options['icon']): ?>
                        <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($options['title']) ?>
                </h2>
            <?php endif; ?>

            <?php if (is_array($options['content'])): ?>
                <?php foreach ($options['content'] as $paragraph): ?>
                    <p><?= htmlspecialchars($paragraph) ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?= htmlspecialchars($options['content']) ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>