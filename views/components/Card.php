<?php
/**
 * Card Component (Generic Framework Component)
 * Universal card component that can display any type of content
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
            'badgeColor' => PRIMARY_COLOR,  // Use constant
            'title' => '',
            'description' => null,
            'icon' => null,
            'footer' => null,
            'link' => null,
            'cssClass' => '',
            'onClick' => null,
            'spacing' => 'default' // 'default', 'compact', 'spacious'
        ];

        $options = array_merge($defaults, $options);
        
        // Spacing classes
        $spacingClass = $options['spacing'] === 'compact' ? 'card-compact' : 
                       ($options['spacing'] === 'spacious' ? 'card-spacious' : '');
        ?>
        <div class="card <?= htmlspecialchars($options['cssClass']) ?> <?= $spacingClass ?>" 
             <?= $options['onClick'] ? 'onclick="' . htmlspecialchars($options['onClick']) . '"' : '' ?>
             style="display: flex; flex-direction: column; height: 100%; overflow: hidden;">

            <?php if ($options['image']): ?>
                <div class="card-image"
                    style="background-image: url('<?= htmlspecialchars($options['image']) ?>'); 
                           background-size: cover; 
                           background-position: center;
                           height: <?= htmlspecialchars($options['imageHeight']) ?>;
                           flex-shrink: 0;">
                </div>
            <?php endif; ?>

            <div class="card-content" style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column; gap: 0.75rem;">
                
                <?php if ($options['badge']): ?>
                    <span class="card-badge" 
                          style="background: <?= htmlspecialchars($options['badgeColor']) ?>; 
                                 color: white; 
                                 padding: 0.25rem 0.75rem; 
                                 border-radius: 9999px; 
                                 font-size: 0.75rem; 
                                 font-weight: 600; 
                                 display: inline-block; 
                                 width: fit-content;
                                 margin-bottom: 0.5rem;">
                        <?= htmlspecialchars($options['badge']) ?>
                    </span>
                <?php endif; ?>

                <?php if ($options['icon']): ?>
                    <i class="<?= htmlspecialchars($options['icon']) ?> card-icon" 
                       style="font-size: 2rem; color: <?= PRIMARY_COLOR ?>; margin-bottom: 0.5rem;"></i>
                <?php endif; ?>

                <h3 class="card-title" 
                    style="margin: 0; 
                           font-size: 1.25rem; 
                           font-weight: 600; 
                           color: <?= TEXT_DARK ?>; 
                           line-height: 1.4;">
                    <?= htmlspecialchars($options['title']) ?>
                </h3>

                <?php if ($options['description']): ?>
                    <p class="card-description" 
                       style="margin: 0; 
                              color: <?= TEXT_GRAY ?>; 
                              line-height: 1.6; 
                              font-size: 0.95rem;
                              flex: 1;">
                        <?= htmlspecialchars($options['description']) ?>
                    </p>
                <?php endif; ?>

                <?php if ($options['footer']): ?>
                    <div class="card-footer" 
                         style="display: flex; 
                                flex-wrap: wrap; 
                                gap: 1rem; 
                                padding-top: 0.75rem; 
                                border-top: 1px solid <?= BG_GRAY ?>; 
                                margin-top: auto;">
                        <?php foreach ($options['footer'] as $item): ?>
                            <span class="card-footer-item" 
                                  style="display: flex; 
                                         align-items: center; 
                                         gap: 0.5rem; 
                                         color: <?= TEXT_GRAY ?>; 
                                         font-size: 0.875rem;">
                                <?php if (!empty($item['icon'])): ?>
                                    <i class="<?= htmlspecialchars($item['icon']) ?>" 
                                       style="color: <?= PRIMARY_COLOR ?>;"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($item['text']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($options['link']): ?>
                    <a href="<?= htmlspecialchars($options['link']['url']) ?>" 
                       class="card-link"
                       style="display: inline-flex; 
                              align-items: center; 
                              gap: 0.5rem; 
                              color: <?= PRIMARY_COLOR ?>; 
                              text-decoration: none; 
                              font-weight: 600; 
                              margin-top: 0.5rem;
                              transition: gap 0.2s ease;">
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