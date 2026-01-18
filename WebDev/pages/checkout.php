<?php
/**
 * FAQJA E CHECKOUT
 * ================
 * Faqja ku përdoruesi përfundon porosinë.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Checkout';
require_once __DIR__ . '/../includes/header.php';

$db = Database::getInstance();
$userId = getCurrentUserId();

// Merr produktet në shportë
$cartItems = $db->fetchAll(
    "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock
     FROM cart c
     JOIN products p ON c.product_id = p.id
     WHERE c.user_id = ?",
    [$userId]
);

// Nëse shporta është bosh, ridrejto
if (empty($cartItems)) {
    setFlash('warning', 'Shporta juaj është bosh.');
    redirect('pages/cart.php');
}

// Llogarit totalin
$subtotal = 0;
foreach ($cartItems as $item) {
    $price = $item['sale_price'] ?: $item['price'];
    $subtotal += $price * $item['quantity'];
}
$shipping = $subtotal > 50 ? 0 : 5;
$total = $subtotal + $shipping;

// Merr të dhënat e përdoruesit
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <h2 class="mb-4"><i class="bi bi-credit-card me-2"></i>Checkout</h2>

            <form id="checkoutForm" method="POST" action="<?= SITE_URL ?>/api/checkout.php">
                <?= csrfField() ?>

                <!-- Informacionet e Dërgesës -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Adresa e Dërgesës</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">Emri</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Mbiemri</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Telefoni</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Adresa</label>
                                <input type="text" class="form-control" id="address" name="address"
                                       placeholder="Rruga, Numri" required>
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">Qyteti</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="col-md-6">
                                <label for="postal_code" class="form-label">Kodi Postar</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code">
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label">Shënime (opsionale)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"
                                          placeholder="Udhëzime të veçanta për dërgesën..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metoda e Pagesës -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Metoda e Pagesës</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" checked>
                            <label class="form-check-label" for="cash">
                                <i class="bi bi-cash me-2"></i>Pagesë në Dorëzim (Cash on Delivery)
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="bank" value="bank">
                            <label class="form-check-label" for="bank">
                                <i class="bi bi-bank me-2"></i>Transfer Bankar
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" disabled>
                            <label class="form-check-label text-muted" for="card">
                                <i class="bi bi-credit-card me-2"></i>Kartë Krediti/Debiti (Së shpejti)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary btn-lg w-100 py-3">
                    <i class="bi bi-check-circle me-2"></i>Konfirmo Porosinë - <?= formatPrice($total) ?>
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 80px;">
                <div class="card-header">
                    <h5 class="mb-0">Porosia Juaj</h5>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex align-items-center p-3 border-bottom">
                            <img src="<?= IMAGES_URL ?>/products/<?= htmlspecialchars($item['image'] ?? 'default.png') ?>"
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-0 small"><?= htmlspecialchars($item['name']) ?></h6>
                                <small class="text-muted">Sasia: <?= $item['quantity'] ?></small>
                            </div>
                            <strong class="text-primary">
                                <?php
                                $price = $item['sale_price'] ?: $item['price'];
                                echo formatPrice($price * $item['quantity']);
                                ?>
                            </strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Nëntotali:</span>
                        <span><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Transporti:</span>
                        <span><?= $shipping === 0 ? '<span class="text-success">Falas</span>' : formatPrice($shipping) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Totali:</strong>
                        <strong class="text-primary fs-5"><?= formatPrice($total) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
