    </main>
    <!-- Main Content End -->

    <!-- FOOTER -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <!-- About -->
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-globe2 me-2"></i><?= SITE_NAME ?>
                    </h5>
                    <p class="text-muted">
                        Platformë moderne për shërbime dhe produkte cilësore.
                        Na besoni për eksperiencën tuaj më të mirë online.
                    </p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-light fs-5"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light fs-5"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light fs-5"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-light fs-5"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Lidhje të Shpejta</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?= SITE_URL ?>" class="text-muted text-decoration-none">Kryefaqja</a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= SITE_URL ?>/pages/products.php" class="text-muted text-decoration-none">Produktet</a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= SITE_URL ?>/pages/contact.php" class="text-muted text-decoration-none">Kontakt</a>
                        </li>
                    </ul>
                </div>

                <!-- Account -->
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Llogaria</h6>
                    <ul class="list-unstyled">
                        <?php if (isLoggedIn()): ?>
                            <li class="mb-2">
                                <a href="<?= SITE_URL ?>/pages/profile.php" class="text-muted text-decoration-none">Profili</a>
                            </li>
                            <li class="mb-2">
                                <a href="<?= SITE_URL ?>/pages/orders.php" class="text-muted text-decoration-none">Porositë</a>
                            </li>
                        <?php else: ?>
                            <li class="mb-2">
                                <a href="<?= SITE_URL ?>/auth/login.php" class="text-muted text-decoration-none">Hyr</a>
                            </li>
                            <li class="mb-2">
                                <a href="<?= SITE_URL ?>/auth/register.php" class="text-muted text-decoration-none">Regjistrohu</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4">
                    <h6 class="fw-bold mb-3">Kontakt</h6>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt me-2"></i>Rruga Kryesore, Nr. 123, Tiranë
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope me-2"></i><?= SITE_EMAIL ?>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>+355 69 123 4567
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4 border-secondary">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <small class="text-muted">
                        &copy; <?= date('Y') ?> <?= SITE_NAME ?>. Të gjitha të drejtat e rezervuara.
                    </small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small class="text-muted">
                        Krijuar me <i class="bi bi-heart-fill text-danger"></i> në Shqipëri
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary btn-floating">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="<?= JS_URL ?>/app.js"></script>

    <?php if (isset($extraJS)): ?>
        <?= $extraJS ?>
    <?php endif; ?>
</body>
</html>
