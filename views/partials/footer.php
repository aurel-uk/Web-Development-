    </main>
    <!-- Main Content End -->

    <!-- FOOTER -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <!-- About -->
                <div class="col-lg-4">
                    <h5 class="mb-3">
                        <i class="bi bi-globe2 me-2"></i><?= SITE_NAME ?>
                    </h5>
                    <p class="text-muted">
                        Platformë moderne e-commerce e ndërtuar me PHP, MySQL,
                        Bootstrap dhe teknologji të avancuara sigurie.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter-x fs-5"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-linkedin fs-5"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-4">
                    <h6 class="mb-3">Lidhje të Shpejta</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?= SITE_URL ?>" class="text-muted text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> Kryefaqja
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= SITE_URL ?>/views/products.php" class="text-muted text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> Produktet
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= SITE_URL ?>/views/about.php" class="text-muted text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> Rreth Nesh
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= SITE_URL ?>/views/contact.php" class="text-muted text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> Kontakt
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Account -->
                <div class="col-lg-2 col-md-4">
                    <h6 class="mb-3">Llogaria</h6>
                    <ul class="list-unstyled">
                        <?php if (isLoggedIn()): ?>
                            <li class="mb-2">
                                <a href="<?= SITE_URL ?>/views/user/profile.php" class="text-muted text-decoration-none">
                                    <i class="bi bi-chevron-right small"></i> Profili Im
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="<?= SITE_URL ?>/views/user/orders.php" class="text-muted text-decoration-none">
                                    <i class="bi bi-chevron-right small"></i> Porositë
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="mb-2">
                                <a href="<?= SITE_URL ?>/views/auth/login.php" class="text-muted text-decoration-none">
                                    <i class="bi bi-chevron-right small"></i> Hyr
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="<?= SITE_URL ?>/views/auth/register.php" class="text-muted text-decoration-none">
                                    <i class="bi bi-chevron-right small"></i> Regjistrohu
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="mb-2">
                            <a href="<?= SITE_URL ?>/views/cart.php" class="text-muted text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> Shporta
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-lg-4 col-md-4">
                    <h6 class="mb-3">Kontakt</h6>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt me-2"></i>
                            Rruga Kryesore, Nr. 123, Tiranë
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            info@webplatform.com
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            +355 69 123 4567
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock me-2"></i>
                            E Hënë - E Premte: 09:00 - 18:00
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4 border-secondary">

            <!-- Copyright -->
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-muted mb-0">
                        &copy; <?= date('Y') ?> <?= SITE_NAME ?>. Të gjitha të drejtat e rezervuara.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-muted text-decoration-none me-3">Politika e Privatësisë</a>
                    <a href="#" class="text-muted text-decoration-none">Termat & Kushtet</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button type="button" class="btn btn-primary btn-floating" id="btn-back-to-top" style="display: none;">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (për funksionalitete AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Custom JS -->
    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>

    <?php if (isset($extraJS)): ?>
        <?= $extraJS ?>
    <?php endif; ?>
</body>
</html>
