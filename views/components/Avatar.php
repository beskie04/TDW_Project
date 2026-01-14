<?php
/**
 * Avatar Component (Generic Framework Component)
 * User photo/avatar display
 * 
 * @param array $options Configuration:
 *   - 'src' (string|null): Image source URL
 *   - 'alt' (string): Alt text (default: 'Avatar')
 *   - 'size' (string): Size ('xs', 'sm', 'small', 'medium', 'large', 'xl', 'xxl') (default: 'medium')
 *   - 'icon' (string): Fallback icon (default: 'fas fa-user')
 *   - 'shape' (string): 'circle' or 'square' (default: 'circle')
 *   - 'cssClass' (string): Additional CSS classes
 *   - 'name' (string): User name for generating initials fallback
 */

class Avatar
{
    private static $stylesRendered = false;

    public static function render($options = [])
    {
        $defaults = [
            'src' => null,
            'alt' => 'Avatar',
            'size' => 'medium',
            'icon' => 'fas fa-user',
            'shape' => 'circle',
            'cssClass' => '',
            'name' => ''
        ];

        $options = array_merge($defaults, $options);

        // Normalize size names
        $sizeMap = [
            'xs' => 'xs',
            'sm' => 'sm',
            'small' => 'small',
            'medium' => 'medium',
            'large' => 'large',
            'xl' => 'xl',
            'xxl' => 'xxl'
        ];

        $size = $sizeMap[$options['size']] ?? 'medium';
        $sizeClass = 'avatar-' . $size;
        $shapeClass = 'avatar-' . $options['shape'];

        // Generate initials if name is provided
        $initials = '';
        if (!empty($options['name'])) {
            $initials = self::getInitials($options['name']);
        }

        // Render styles once
        if (!self::$stylesRendered) {
            self::renderStyles();
            self::$stylesRendered = true;
        }

        ?>
        <div class="avatar <?= $sizeClass ?> <?= $shapeClass ?> <?= htmlspecialchars($options['cssClass']) ?>">
            <?php if ($options['src']): ?>
                <img 
                    src="<?= htmlspecialchars($options['src']) ?>" 
                    alt="<?= htmlspecialchars($options['alt']) ?>"
                    loading="lazy"
                    onerror="this.style.display='none'; this.parentElement.classList.add('avatar-fallback');"
                >
            <?php endif; ?>
            
            <?php if (!$options['src'] || $initials): ?>
                <div class="avatar-fallback-content">
                    <?php if ($initials): ?>
                        <span class="avatar-initials"><?= htmlspecialchars($initials) ?></span>
                    <?php else: ?>
                        <i class="<?= htmlspecialchars($options['icon']) ?>"></i>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Get initials from name
     */
    private static function getInitials($name)
    {
        if (empty($name)) {
            return '';
        }

        $parts = array_filter(explode(' ', trim($name)));
        
        if (count($parts) >= 2) {
            // First letter of first name + first letter of last name
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
        } else {
            // Just first two letters of single name
            return strtoupper(substr($name, 0, 2));
        }
    }

    /**
     * Render CSS styles
     */
    private static function renderStyles()
    {
        ?>
        <style>
            /* Avatar Base Styles */
            .avatar {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                flex-shrink: 0;
                border: 3px solid white;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                transition: all 0.2s ease;
            }

            /* Shape Variants */
            .avatar-circle {
                border-radius: 50%;
            }

            .avatar-square {
                border-radius: 8px;
            }

            /* Avatar Image - CRITICAL FOR CONSISTENT SIZING */
            .avatar img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
                display: block;
                position: absolute;
                top: 0;
                left: 0;
            }

            /* Fallback Content */
            .avatar-fallback-content {
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 600;
            }

            .avatar.avatar-fallback .avatar-fallback-content {
                display: flex !important;
            }

            .avatar:not(.avatar-fallback) .avatar-fallback-content {
                display: none;
            }

            /* Initials */
            .avatar-initials {
                text-transform: uppercase;
                user-select: none;
            }

            /* Icon fallback */
            .avatar i {
                color: white;
                opacity: 0.9;
            }

            /* Size Variants */
            .avatar-xs {
                width: 32px;
                height: 32px;
                border-width: 2px;
            }

            .avatar-xs .avatar-initials {
                font-size: 0.625rem;
            }

            .avatar-xs i {
                font-size: 0.75rem;
            }

            .avatar-sm {
                width: 48px;
                height: 48px;
                border-width: 2px;
            }

            .avatar-sm .avatar-initials {
                font-size: 0.75rem;
            }

            .avatar-sm i {
                font-size: 1rem;
            }

            .avatar-small {
                width: 64px;
                height: 64px;
            }

            .avatar-small .avatar-initials {
                font-size: 1rem;
            }

            .avatar-small i {
                font-size: 1.5rem;
            }

            .avatar-medium {
                width: 80px;
                height: 80px;
            }

            .avatar-medium .avatar-initials {
                font-size: 1.25rem;
            }

            .avatar-medium i {
                font-size: 2rem;
            }

            .avatar-large {
                width: 96px;
                height: 96px;
            }

            .avatar-large .avatar-initials {
                font-size: 1.5rem;
            }

            .avatar-large i {
                font-size: 2.5rem;
            }

            .avatar-xl {
                width: 128px;
                height: 128px;
            }

            .avatar-xl .avatar-initials {
                font-size: 2rem;
            }

            .avatar-xl i {
                font-size: 3rem;
            }

            .avatar-xxl {
                width: 160px;
                height: 160px;
            }

            .avatar-xxl .avatar-initials {
                font-size: 2.5rem;
            }

            .avatar-xxl i {
                font-size: 4rem;
            }

            /* Hover Effect */
            .avatar:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
                transform: scale(1.05);
            }

            /* No hover on very small avatars */
            .avatar-xs:hover,
            .avatar-sm:hover {
                transform: scale(1.02);
            }

            /* Loading state */
            .avatar.loading {
                background: #e5e7eb;
                animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }

            @keyframes pulse {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.5;
                }
            }
        </style>
        <?php
    }
}