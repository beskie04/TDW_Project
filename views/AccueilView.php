<?php
require_once __DIR__ . '/BaseView.php';

class AccueilView extends BaseView
{

    public function __construct()
    {
        $this->currentPage = 'accueil';
        $this->pageTitle = 'Accueil - Laboratoire Universitaire';
    }

    /**
     * Page d'accueil complète
     */
    public function render($actualites, $publications, $projets, $equipes)
    {
        $this->renderHeader();
        $this->renderFlashMessage();
        ?>

        <main>
            <!-- Diaporama des actualités -->
            <?php if (!empty($actualites)): ?>
                <section class="hero-slider">
                    <div class="slider-container">
                        <?php foreach ($actualites as $index => $actu): ?>
                            <div class="slide <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
                                <?php if (!empty($actu['image'])): ?>
                                    <div class="slide-image"
                                        style="background-image: url('<?= UPLOADS_URL . 'actualites/' . $actu['image'] ?>')"></div>
                                <?php else: ?>
                                    <div class="slide-image"
                                        style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color))"></div>
                                <?php endif; ?>
                                <div class="slide-overlay"></div>
                                <div class="container">
                                    <div class="slide-content">
                                        <span class="slide-type"><?= htmlspecialchars($actu['type']) ?></span>
                                        <h2><?= htmlspecialchars($actu['titre']) ?></h2>
                                        <p><?= htmlspecialchars($actu['description']) ?></p>
                                        <?php if (!empty($actu['lien'])): ?>
                                            <a href="<?= htmlspecialchars($actu['lien']) ?>" class="btn-primary">
                                                En savoir plus <i class="fas fa-arrow-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Navigation du slider -->
                    <div class="slider-nav">
                        <button class="slider-prev"><i class="fas fa-chevron-left"></i></button>
                        <div class="slider-dots">
                            <?php foreach ($actualites as $index => $actu): ?>
                                <span class="dot <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>"></span>
                            <?php endforeach; ?>
                        </div>
                        <button class="slider-next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Zone de contenu -->
            <div class="content-wrapper">
                <div class="container">

                    <!-- Section Actualités scientifiques -->
                    <section class="home-section">
                        <div class="section-header">
                            <h2><i class="fas fa-newspaper"></i> Actualités Scientifiques</h2>
                            <a href="?page=actualites" class="section-link">Voir tout <i class="fas fa-arrow-right"></i></a>
                        </div>

                        <div class="actualites-grid">
                            <?php foreach (array_slice($actualites, 0, 3) as $actu): ?>
                                <div class="actualite-card">
                                    <?php if (!empty($actu['image'])): ?>
                                        <div class="actualite-image"
                                            style="background-image: url('<?= UPLOADS_URL . 'actualites/' . $actu['image'] ?>')"></div>
                                    <?php endif; ?>
                                    <div class="actualite-content">
                                        <span class="actualite-type"><?= htmlspecialchars($actu['type']) ?></span>
                                        <h3><?= htmlspecialchars($actu['titre']) ?></h3>
                                        <p><?= htmlspecialchars(mb_substr($actu['description'], 0, 120)) ?>...</p>
                                        <div class="actualite-footer">
                                            <span class="actualite-date">
                                                <i class="fas fa-calendar"></i>
                                                <?= date('d/m/Y', strtotime($actu['date_publication'])) ?>
                                            </span>
                                            <?php if (!empty($actu['lien'])): ?>
                                                <a href="<?= htmlspecialchars($actu['lien']) ?>" class="actualite-link">
                                                    Lire plus <i class="fas fa-arrow-right"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Présentation du laboratoire -->
                    <section class="lab-presentation">
                        <h2><i class="fas fa-flask"></i> Présentation du Laboratoire</h2>
                        <p>
                            Le Laboratoire de Recherche en Informatique de l'École Supérieure d'Informatique est un centre
                            d'excellence
                            dédié à l'innovation et à la recherche de pointe dans divers domaines de l'informatique. Nos équipes
                            travaillent
                            sur des problématiques actuelles telles que l'intelligence artificielle, la cybersécurité, le cloud
                            computing,
                            les réseaux et les systèmes embarqués.
                        </p>
                        <p>
                            Fort d'une équipe de chercheurs expérimentés et de doctorants talentueux, le laboratoire collabore
                            avec
                            des partenaires académiques et industriels nationaux et internationaux pour produire des résultats
                            de
                            recherche
                            de haut niveau et former la prochaine génération d'experts en informatique.
                        </p>
                    </section>



                    <!-- Section evenements a venir -->
                    <section class="home-section">
                        <div class="section-header">
                            <h2><i class="fas fa-project-diagram"></i> evenements a venir</h2>
                            <a href="?page=projets" class="section-link">Voir tout <i class="fas fa-arrow-right"></i></a>
                        </div>

                        <div class="projets-home-grid">
                            <?php foreach ($projets as $projet): ?>
                                <div class="projet-home-card">
                                    <span class="projet-thematique-small">
                                        <?= htmlspecialchars($projet['thematique_nom'] ?? 'N/A') ?>
                                    </span>
                                    <h4><?= htmlspecialchars($projet['titre']) ?></h4>
                                    <p><?= htmlspecialchars(mb_substr($projet['description'], 0, 100)) ?>...</p>
                                    <a href="?page=projets&action=details&id=<?= $projet['id_projet'] ?>" class="projet-link-small">
                                        Voir le projet <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Section Partenaires -->
                    <section class="home-section partenaires-section">
                        <div class="section-header">
                            <h2><i class="fas fa-handshake"></i> Nos Partenaires</h2>
                        </div>

                        <div class="partenaires-grid">
                            <div class="partenaire-card">
                                <i class="fas fa-university"></i>
                                <h4>Universités</h4>
                                <p>Partenariats académiques nationaux et internationaux</p>
                            </div>
                            <div class="partenaire-card">
                                <i class="fas fa-building"></i>
                                <h4>Entreprises</h4>
                                <p>Collaboration avec des entreprises innovantes</p>
                            </div>
                            <div class="partenaire-card">
                                <i class="fas fa-globe"></i>
                                <h4>Organismes</h4>
                                <p>Coopération avec des organismes de recherche</p>
                            </div>
                        </div>
                    </section>

                </div>
            </div>
        </main>

        <script>
            // Slider automatique
            let currentSlide = 0;
            const slides = document.querySelectorAll('.slide');
            const dots = document.querySelectorAll('.dot');
            const totalSlides = slides.length;

            function showSlide(n) {
                slides.forEach(s => s.classList.remove('active'));
                dots.forEach(d => d.classList.remove('active'));

                currentSlide = (n + totalSlides) % totalSlides;
                slides[currentSlide].classList.add('active');
                dots[currentSlide].classList.add('active');
            }

            function nextSlide() {
                showSlide(currentSlide + 1);
            }

            function prevSlide() {
                showSlide(currentSlide - 1);
            }

            // Auto-play toutes les 5 secondes
            let autoPlay = setInterval(nextSlide, 5000);

            // Navigation manuelle
            document.querySelector('.slider-next')?.addEventListener('click', () => {
                clearInterval(autoPlay);
                nextSlide();
                autoPlay = setInterval(nextSlide, 5000);
            });

            document.querySelector('.slider-prev')?.addEventListener('click', () => {
                clearInterval(autoPlay);
                prevSlide();
                autoPlay = setInterval(nextSlide, 5000);
            });

            // Dots navigation
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    clearInterval(autoPlay);
                    showSlide(index);
                    autoPlay = setInterval(nextSlide, 5000);
                });
            });
        </script>

        <?php
        $this->renderFooter();
    }
}
?>