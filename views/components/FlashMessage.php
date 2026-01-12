<?php

class FlashMessage
{
    private static string $sessionKey = 'flash_message';
    private static string $sessionTypeKey = 'flash_type';

    /**
     * Configuration des types de messages
     */
    private static array $types = [
        'success' => [
            'icon' => 'fas fa-check-circle',
            'class' => 'flash-success'
        ],
        'error' => [
            'icon' => 'fas fa-exclamation-circle',
            'class' => 'flash-error'
        ],
        'warning' => [
            'icon' => 'fas fa-exclamation-triangle',
            'class' => 'flash-warning'
        ],
        'info' => [
            'icon' => 'fas fa-info-circle',
            'class' => 'flash-info'
        ]
    ];

    /**
     * Définir un message flash
     */
    public static function set(string $message, string $type = 'success'): void
    {
        $_SESSION[self::$sessionKey] = $message;
        $_SESSION[self::$sessionTypeKey] = $type;
    }

    /**
     * Récupérer et supprimer le message flash
     */
    public static function get(): ?array
    {
        if (!isset($_SESSION[self::$sessionKey])) {
            return null;
        }

        $message = $_SESSION[self::$sessionKey];
        $type = $_SESSION[self::$sessionTypeKey] ?? 'info';

        // Supprimer après lecture
        unset($_SESSION[self::$sessionKey], $_SESSION[self::$sessionTypeKey]);

        return [
            'message' => $message,
            'type' => $type,
            'config' => self::$types[$type] ?? self::$types['info']
        ];
    }

    /**
     * Vérifier si un message existe
     */
    public static function has(): bool
    {
        return isset($_SESSION[self::$sessionKey]);
    }

    /**
     * Rendu du message flash
     */
    public static function render(array $config = []): void
    {
        $flash = self::get();
        
        if ($flash === null) {
            return;
        }

        $showIcon = $config['show_icon'] ?? true;
        $showClose = $config['show_close'] ?? true;
        $autoClose = $config['auto_close'] ?? true;
        $autoCloseDelay = $config['auto_close_delay'] ?? 5000;
        $customClass = $config['class'] ?? '';

        $typeConfig = $flash['config'];
        $classes = trim("flash-message {$typeConfig['class']} {$customClass}");
        ?>
        <div class="<?= htmlspecialchars($classes) ?>" 
             <?php if ($autoClose): ?>
             data-auto-close="<?= $autoCloseDelay ?>"
             <?php endif; ?>>
            
            <?php if ($showIcon): ?>
                <i class="flash-icon <?= htmlspecialchars($typeConfig['icon']) ?>"></i>
            <?php endif; ?>
            
            <span class="flash-text">
                <?= htmlspecialchars($flash['message']) ?>
            </span>
            
            <?php if ($showClose): ?>
                <button class="close-flash" aria-label="Close">
                    &times;
                </button>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Rendu avec configuration personnalisée
     */
    public static function renderCustom(string $message, string $type = 'info', array $config = []): void
    {
        if (!isset(self::$types[$type])) {
            $type = 'info';
        }

        $typeConfig = self::$types[$type];
        $showIcon = $config['show_icon'] ?? true;
        $showClose = $config['show_close'] ?? true;
        $customClass = $config['class'] ?? '';

        $classes = trim("flash-message {$typeConfig['class']} {$customClass}");
        ?>
        <div class="<?= htmlspecialchars($classes) ?>">
            <?php if ($showIcon): ?>
                <i class="flash-icon <?= htmlspecialchars($typeConfig['icon']) ?>"></i>
            <?php endif; ?>
            
            <span class="flash-text">
                <?= htmlspecialchars($message) ?>
            </span>
            
            <?php if ($showClose): ?>
                <button class="close-flash" aria-label="Close">
                    &times;
                </button>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Ajouter un type de message personnalisé
     */
    public static function addType(string $name, string $icon, string $class): void
    {
        self::$types[$name] = [
            'icon' => $icon,
            'class' => $class
        ];
    }
}