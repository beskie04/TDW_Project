<?php
/**
 * Card Component (Generic Framework Component)
 * Universal card component that can display any type of content
 * 
 * @param array $options Configuration array:
 *   - 'image' (string|null): Image URL
 *   - 'imageHeight' (string): CSS height for image (default: '200px')
 *   - 'badge' (string|null): Badge text
 *   - 'badgeColor' (string): Badge background color (default: 'var(--primary-color)')
 *   - 'title' (string): Card title
 *   - 'description' (string|null): Card description
 *   - 'icon' (string|null): FontAwesome icon class
 *   - 'footer' (array|null): Footer items [['icon' => '', 'text' => ''], ...]
 *   - 'link' (array|null): Link ['url' => '', 'text' => '', 'icon' => '']
 *   - 'cssClass' (string): Additional CSS classes
 *   - 'onClick' (string|null): JavaScript onClick handler
 */

class Card
{
    public static function render($options = [])
    {
        // Default values
        $defaults = [
            'image' => null,
            'imageHeight' => '200px',
            'badge' => null,
            'badgeColor' => 'var(--primary-color)',
            'title' => '',
            'description' => null,
            'icon' => null,
            'footer' => null,
            'link' => null,
            'cssClass' => '',
            'onClick' => null
        ];

        $options = array_merge($defaults, $options);
        ?>
        <div class="card <?= htmlspecialchars($options['cssClass']) ?>" <?= $options['onClick'] ? 'onclick="' . htmlspecialchars($options['onClick']) . '"' : '' ?>>

            <?php if ($options['image']): ?>
                <div class="card-image"
                    style="background-image: url('<?= htmlspecialchars($options['image']) ?>'); height: <?= htmlspecialchars($options['imageHeight']) ?>;">
                </div>
            <?php endif; ?>

            <div class="card-content">
                <?php if ($options['badge']): ?>
                    <span class="card-badge" style="background: <?= htmlspecialchars($options['badgeColor']) ?>;">
                        <?= htmlspecialchars($options['badge']) ?>
                    </span>
                <?php endif; ?>

                <?php if ($options['icon']): ?>
                    <i class="<?= htmlspecialchars($options['icon']) ?> card-icon"></i>
                <?php endif; ?>

                <h3 class="card-title"><?= htmlspecialchars($options['title']) ?></h3>

                <?php if ($options['description']): ?>
                    <p class="card-description"><?= htmlspecialchars($options['description']) ?></p>
                <?php endif; ?>

                <?php if ($options['footer']): ?>
                    <div class="card-footer">
                        <?php foreach ($options['footer'] as $item): ?>
                            <span class="card-footer-item">
                                <?php if (!empty($item['icon'])): ?>
                                    <i class="<?= htmlspecialchars($item['icon']) ?>"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($item['text']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($options['link']): ?>
                    <a href="<?= htmlspecialchars($options['link']['url']) ?>" class="card-link">
                        <?= htmlspecialchars($options['link']['text']) ?>
                        <?php if (!empty($options['link']['icon'])): ?>
                            <i class="<?= htmlspecialchars($options['link']['icon']) ?>"></i>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
?>