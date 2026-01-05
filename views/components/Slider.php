<?php
/**
 * Slider Component (Generic Framework Component)
 * Fully customizable hero/carousel slider
 * 
 * @param array $slides Array of slide data:
 *   Each slide: ['image' => '', 'title' => '', 'description' => '', 'badge' => '', 'link' => ['url' => '', 'text' => '']]
 * @param array $options Configuration:
 *   - 'height' (string): Slider height (default: '600px')
 *   - 'autoplay' (bool): Enable autoplay (default: true)
 *   - 'autoplayDelay' (int): Milliseconds between slides (default: 5000)
 *   - 'showControls' (bool): Show prev/next buttons (default: true)
 *   - 'showDots' (bool): Show dot indicators (default: true)
 *   - 'overlayOpacity' (float): Overlay opacity 0-1 (default: 0.5)
 *   - 'cssClass' (string): Additional CSS classes
 */

class Slider
{
    public static function render($slides, $options = [])
    {
        if (empty($slides))
            return;

        $defaults = [
            'height' => '600px',
            'autoplay' => true,
            'autoplayDelay' => 5000,
            'showControls' => true,
            'showDots' => true,
            'overlayOpacity' => 0.5,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);
        $sliderId = 'slider-' . uniqid();
        ?>
        <div class="slider <?= htmlspecialchars($options['cssClass']) ?>" id="<?= $sliderId ?>"
            style="height: <?= htmlspecialchars($options['height']) ?>;">

            <div class="slider-container">
                <?php foreach ($slides as $index => $slide): ?>
                    <div class="slider-slide <?= $index === 0 ? 'active' : '' ?>">
                        <?php if (!empty($slide['image'])): ?>
                            <div class="slider-image" style="background-image: url('<?= htmlspecialchars($slide['image']) ?>');"></div>
                        <?php else: ?>
                            <div class="slider-image"
                                style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));"></div>
                        <?php endif; ?>

                        <div class="slider-overlay" style="opacity: <?= $options['overlayOpacity'] ?>;"></div>

                        <div class="container">
                            <div class="slider-content">
                                <?php if (!empty($slide['badge'])): ?>
                                    <span class="slider-badge"><?= htmlspecialchars($slide['badge']) ?></span>
                                <?php endif; ?>

                                <h2><?= htmlspecialchars($slide['title']) ?></h2>

                                <?php if (!empty($slide['description'])): ?>
                                    <p><?= htmlspecialchars($slide['description']) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($slide['link'])): ?>
                                    <a href="<?= htmlspecialchars($slide['link']['url']) ?>" class="btn btn-primary">
                                        <?= htmlspecialchars($slide['link']['text'] ?? 'En savoir plus') ?>
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($options['showControls']): ?>
                <div class="slider-controls">
                    <button class="slider-btn slider-prev">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="slider-btn slider-next">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($options['showDots']): ?>
                <div class="slider-dots">
                    <?php foreach ($slides as $index => $slide): ?>
                        <span class="slider-dot <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>"></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <script>
            (function () {
                const slider = document.getElementById('<?= $sliderId ?>');
                if (!slider) return;

                let currentSlide = 0;
                const slides = slider.querySelectorAll('.slider-slide');
                const dots = slider.querySelectorAll('.slider-dot');
                const total = slides.length;

                function show(n) {
                    slides.forEach(s => s.classList.remove('active'));
                    dots.forEach(d => d.classList.remove('active'));
                    currentSlide = (n + total) % total;
                    slides[currentSlide].classList.add('active');
                    if (dots[currentSlide]) dots[currentSlide].classList.add('active');
                }

                function next() { show(currentSlide + 1); }
                function prev() { show(currentSlide - 1); }

                <?php if ($options['autoplay']): ?>
                    let auto = setInterval(next, <?= $options['autoplayDelay'] ?>);
                <?php endif; ?>

                <?php if ($options['showControls']): ?>
                    slider.querySelector('.slider-next')?.addEventListener('click', () => {
                        <?php if ($options['autoplay']): ?>
                            clearInterval(auto);
                            auto = setInterval(next, <?= $options['autoplayDelay'] ?>);
                        <?php endif; ?>
                        next();
                    });

                    slider.querySelector('.slider-prev')?.addEventListener('click', () => {
                        <?php if ($options['autoplay']): ?>
                            clearInterval(auto);
                            auto = setInterval(next, <?= $options['autoplayDelay'] ?>);
                        <?php endif; ?>
                        prev();
                    });
                <?php endif; ?>

                <?php if ($options['showDots']): ?>
                    dots.forEach((dot, i) => {
                        dot.addEventListener('click', () => {
                            <?php if ($options['autoplay']): ?>
                                clearInterval(auto);
                                auto = setInterval(next, <?= $options['autoplayDelay'] ?>);
                            <?php endif; ?>
                            show(i);
                        });
                    });
                <?php endif; ?>
            })();
        </script>
        <?php
    }
}
?>