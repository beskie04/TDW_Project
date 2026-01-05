<?php
/**
 * ProfileCard Component (Generic Framework Component)
 * Large profile card for displaying user/member information
 * 
 * @param array $options Configuration:
 *   - 'photo' (string|null): Photo URL
 *   - 'name' (string): Full name
 *   - 'title' (string|null): Job title/position
 *   - 'subtitle' (string|null): Additional subtitle (grade, etc.)
 *   - 'email' (string|null): Email address
 *   - 'bio' (string|null): Biography text
 *   - 'actions' (array|null): Action buttons [['text' => '', 'icon' => '', 'href' => ''], ...]
 *   - 'layout' (string): 'horizontal' or 'vertical' (default: 'horizontal')
 *   - 'size' (string): 'small', 'medium', 'large' (default: 'medium')
 *   - 'cssClass' (string): Additional CSS classes
 */

class ProfileCard
{
    public static function render($options = [])
    {
        $defaults = [
            'photo' => null,
            'name' => '',
            'title' => null,
            'subtitle' => null,
            'email' => null,
            'bio' => null,
            'actions' => null,
            'layout' => 'horizontal',
            'size' => 'medium',
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        $layoutClass = 'profile-' . $options['layout'];
        $sizeClass = 'profile-' . $options['size'];
        ?>
        <div class="profile-card <?= $layoutClass ?> <?= $sizeClass ?> <?= htmlspecialchars($options['cssClass']) ?>">
            <!-- Photo -->
            <div class="profile-photo">
                <?php if ($options['photo']): ?>
                    <img src="<?= htmlspecialchars($options['photo']) ?>" alt="<?= htmlspecialchars($options['name']) ?>">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="profile-info">
                <h3 class="profile-name"><?= htmlspecialchars($options['name']) ?></h3>

                <?php if ($options['title']): ?>
                    <p class="profile-title"><?= htmlspecialchars($options['title']) ?></p>
                <?php endif; ?>

                <?php if ($options['subtitle']): ?>
                    <p class="profile-subtitle"><?= htmlspecialchars($options['subtitle']) ?></p>
                <?php endif; ?>

                <?php if ($options['email']): ?>
                    <p class="profile-email">
                        <i class="fas fa-envelope"></i>
                        <?= htmlspecialchars($options['email']) ?>
                    </p>
                <?php endif; ?>

                <?php if ($options['bio']): ?>
                    <p class="profile-bio"><?= htmlspecialchars($options['bio']) ?></p>
                <?php endif; ?>

                <?php if ($options['actions']): ?>
                    <div class="profile-actions">
                        <?php foreach ($options['actions'] as $action): ?>
                            <a href="<?= htmlspecialchars($action['href']) ?>" class="btn btn-secondary btn-small">
                                <?php if (!empty($action['icon'])): ?>
                                    <i class="<?= htmlspecialchars($action['icon']) ?>"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($action['text']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
?>