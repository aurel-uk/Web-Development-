<?php
/**
 * FAQJA E SHPORTËS
 * =================
 * Shfaq artikujt në shportë dhe lejon checkout.
 */

$pageTitle = 'Shporta - ' . SITE_NAME;
require_once __DIR__ . '/partials/header.php';

$productObj = new Product();

// Merr shportën (sipas user ose session)
$userId = getCurrentUserId();
$sessionId = $userId ? null : session_id();

$cart = $productObj->getCart($userId, $sessionId);
$cartTotal = $productObj->getCartTotal($userId, $sessionId);

// Proces veprimet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'update' && isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $cartId => $quantity) {
            $productObj->updateCartQuantity((int)$cartId, (int)$quantity);
        }
        setFlash('success', 'Shporta u përditësua');
        redirect('views/cart.php');
    }

    if ($action === 'remove' && isset($_POST['cart_id'])) {
        $productObj->removeFromCart((int)$_POST['cart_id']);
        setFlash('success', 'Artikulli u hoq nga shporta');
        redirect('views/cart.php');
    }

    if ($action === 'clear') {
        $productObj->clearCart($userId, $sessionId);
        setFlash('success', 'Shporta u pastrua');
        redirect('views/cart.php');
    }
}

// Llogarit vlerat
$subtotal = $cartTotal;
$tax = $subtotal * 0.20;
$shipping = $subtotal > 50 ? 0 : 5;
$total = $subtotal + $tax + $shipping;
?>

<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-cart3 me-2"></i>Shporta Juaj</h2>

    <?php if (empty($cart)): ?>
        <!-- Empty Cart -->
        <div class="text-center py-5">
            <i class="bi bi-cart-x display-1 text-muted"></i>
            <h4 class="mt-3">Shporta është bosh</h4>
            <p class="text-muted">Nuk keni asnjë produkt në shportë.</p>
            <a href="products.php" class="btn btn-primary btn-lg">
                <i class="bi bi-bag-plus me-2"></i>Shfleto Produktet
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= count($cart) ?> Artikuj</h5>
                        <form method="POST" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Je i sigurt?')">
                                <i class="bi bi-trash me-1"></i>Pastro Shportën
                            </button>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <form method="POST" id="cartForm">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="update">

                            <?php foreach ($cart as $item): ?>
                                <?php $itemPrice = $item['sale_price'] ?? $item['price']; ?>
                                <div class="cart-item d-flex align-items-center">
                                    <!-- Image -->
                                    <img src="<?= SITE_URL ?>/assets/images/uploads/<?= $item['image'] ?? 'products/default.png' ?>"
                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                         class="cart-item-image me-3">

                                    <!-- Details -->
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                        <p class="text-muted mb-0 small">
                                            <?= formatPrice($itemPrice) ?> / njësi
                                        </p>
                                    </div>

                                    <!-- Quantity -->
                                    <div class="mx-3">
                                        <input type="number"
                                               class="form-control cart-quantity"
                                               name="quantities[<?= $item['id'] ?>]"
                                               value="<?= $item['quantity'] ?>"
                                               min="1"
                                               max="<?= $item['stock'] ?>">
                                    </div>

                                    <!-- Item Total -->
                                    <div class="text-end me-3" style="min-width: 80px;">
                                        <strong><?= formatPrice($itemPrice * $item['quantity']) ?></strong>
                                    </div>

                                    <!-- Remove -->
                                    <form method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </form>
                    </div>
                    <div class="card-footer bg-white">
                        <button type="submit" form="cartForm" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-repeat me-2"></i>Përditëso Shportën
                        </button>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 80px;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Përmbledhja</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Nën-totali:</span>
                            <span><?= formatPrice($subtotal) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>TVSH (20%):</span>
                            <span><?= formatPrice($tax) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Transporti:</span>
                            <span>
                                <?php if ($shipping === 0): ?>
                                    <span class="text-success">Falas</span>
                                <?php else: ?>
                                    <?= formatPrice($shipping) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if ($subtotal < 50): ?>
                            <small class="text-muted d-block mb-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Shto <?= formatPrice(50 - $subtotal) ?> për transport falas
                            </small>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Totali:</strong>
                            <strong class="text-primary fs-5"><?= formatPrice($total) ?></strong>
                        </div>

                        <?php if (isLoggedIn()): ?>
                            <a href="checkout.php" class="btn btn-primary w-100 btn-lg">
                                <i class="bi bi-credit-card me-2"></i>Vazhdo me Pagesën
                            </a>
                        <?php else: ?>
                            <a href="auth/login.php?redirect=cart.php" class="btn btn-primary w-100 btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Hyr për të Vazhduar
                            </a>
                            <p class="text-center text-muted small mt-2">
                                Duhet të jesh i loguar për të bërë porosi
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Continue Shopping -->
                <div class="text-center mt-3">
                    <a href="products.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-2"></i>Vazhdo Blerjen
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
