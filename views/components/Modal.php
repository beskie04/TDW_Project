<?php
/**
 * Modal Component (Generic Framework Component)
 * Reusable modal dialog for forms, confirmations, content
 * 
 * @param array $options Configuration:
 *   - 'id' (string): Modal unique ID
 *   - 'title' (string): Modal title
 *   - 'size' (string): Size ('small', 'medium', 'large', 'xlarge') (default: 'medium')
 *   - 'closeButton' (bool): Show close button (default: true)
 *   - 'backdrop' (bool): Click outside to close (default: true)
 *   - 'cssClass' (string): Additional CSS classes
 * @param callable $content Callback that renders modal body content
 * @param callable $footer Optional callback for modal footer (buttons)
 */

class Modal
{
    public static function render($options = [], $content = null, $footer = null)
    {
        $defaults = [
            'id' => 'modal-' . uniqid(),
            'title' => 'Modal',
            'size' => 'medium',
            'closeButton' => true,
            'backdrop' => true,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        $sizeClass = 'modal-' . $options['size'];
        $modalId = htmlspecialchars($options['id']);
        ?>

        <div id="<?= $modalId ?>" class="modal <?= htmlspecialchars($options['cssClass']) ?>" style="display: none;">
            <div class="modal-backdrop" onclick="<?= $options['backdrop'] ? "closeModal('{$modalId}')" : '' ?>"></div>

            <div class="modal-dialog <?= $sizeClass ?>">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h3 class="modal-title"><?= htmlspecialchars($options['title']) ?></h3>
                        <?php if ($options['closeButton']): ?>
                            <button type="button" class="modal-close" onclick="closeModal('<?= $modalId ?>')">
                                <i class="fas fa-times"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <?php if ($content && is_callable($content))
                            $content(); ?>
                    </div>

                    <!-- Modal Footer -->
                    <?php if ($footer && is_callable($footer)): ?>
                        <div class="modal-footer">
                            <?php $footer(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <style>
            .modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 9999;
                overflow-y: auto;
                animation: fadeIn 0.3s ease;
            }

            .modal-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
            }

            .modal-dialog {
                position: relative;
                margin: 2rem auto;
                max-width: 500px;
                animation: slideDown 0.3s ease;
            }

            .modal-medium {
                max-width: 600px;
            }

            .modal-large {
                max-width: 800px;
            }

            .modal-xlarge {
                max-width: 1000px;
            }

            .modal-small {
                max-width: 400px;
            }

            .modal-content {
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                overflow: hidden;
            }

            .modal-header {
                padding: 1.5rem;
                border-bottom: 1px solid #e2e8f0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #f7fafc;
            }

            .modal-title {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 600;
                color: #2d3748;
            }

            .modal-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                color: #718096;
                cursor: pointer;
                padding: 0.25rem;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 6px;
                transition: all 0.2s;
            }

            .modal-close:hover {
                background: #e2e8f0;
                color: #2d3748;
            }

            .modal-body {
                padding: 1.5rem;
                max-height: calc(100vh - 200px);
                overflow-y: auto;
            }

            .modal-footer {
                padding: 1rem 1.5rem;
                border-top: 1px solid #e2e8f0;
                display: flex;
                justify-content: flex-end;
                gap: 0.75rem;
                background: #f7fafc;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-50px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @media (max-width: 768px) {
                .modal-dialog {
                    margin: 0;
                    max-width: 100%;
                    min-height: 100vh;
                }

                .modal-content {
                    border-radius: 0;
                    min-height: 100vh;
                }
            }
        </style>

        <script>
            function openModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';

                    // Reset form if exists
                    const form = modal.querySelector('form');
                    if (form) form.reset();
                }
            }

            // Close on ESC key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    const modals = document.querySelectorAll('.modal[style*="display: block"]');
                    modals.forEach(modal => closeModal(modal.id));
                }
            });
        </script>
        <?php
    }
}
?>