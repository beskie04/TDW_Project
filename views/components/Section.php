<?php
/**
 * Section Component (Generic Framework Component)
 * Universal section wrapper with optional header
 * 
 * @param array $options Configuration:
 *   - 'title' (string|null): Section title
 *   - 'icon' (string|null): FontAwesome icon
 *   - 'link' (array|null): ['url' => '', 'text' => '', 'icon' => '']
 *   - 'cssClass' (string): Additional CSS classes
 * @param callable $content Callback function that renders the content
 */

class Section
{
    public static function render($options = [], $content = null)
    {
        $defaults = [
            'title' => null,
            'icon' => null,
            'link' => null,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);
        ?>
        <section class="section <?= htmlspecialchars($options['cssClass']) ?>">
            <?php if ($options['title']): ?>
                <div class="section-header">
                    <h2>
                        <?php if ($options['icon']): ?>
                            <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($options['title']) ?>
                    </h2>
                    <?php if ($options['link']): ?>
                        <a href="<?= htmlspecialchars($options['link']['url']) ?>" class="section-link">
                            <?= htmlspecialchars($options['link']['text']) ?>
                            <?php if (!empty($options['link']['icon'])): ?>
                                <i class="<?= htmlspecialchars($options['link']['icon']) ?>"></i>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="section-content">
                <?php if ($content && is_callable($content))
                    $content(); ?>
            </div>
        </section>
        <?php
    }
}
?>