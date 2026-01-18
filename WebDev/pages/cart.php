<?php
/**
 * SHPORTA E BLERJEVE
 * ==================
 * Faqja e shportës ku përdoruesi shikon produktet e zgjedhura.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Shporta';
require_once __DIR__ . '/../includes/header.php';

$db = Database::getInstance();

// Merr produktet në shportë
$cartItems = $db->fetchAll(
    "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock
     FROM cart c
     JOIN products p ON c.product_id = p.id
     WHERE c.user_id = ?
     ORDER BY c.created_at DESC",
    [getCurrentUserId()]
);

// Llogarit totalin
$subtotal = 0;
foreach ($cartItems as $item) {
    $price = $item['sale_price'] ?: $item['price'];
    $subtotal += $price * $item['quantity'];
}

$shipping = $subtotal > 50 ? 0 : 5; // Transport falas mbi 50€
$total = $subtotal + $shipping;
?>

<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-cart3 me-2"></i>Shporta Ime</h2>

    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x display-1 text-muted"></i>
            <h4 class="mt-3">Shporta juaj është bosh</h4>
            <p class="text-muted">Filloni të shtoni produkte në shportë.</p>
            <a href="products.php" class="btn btn-primary">
                <i class="bi bi-box me-1"></i>Shiko Produktet
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-0">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item d-flex align-items-center" data-product-id="<?= $item['product_id'] ?>">
                                <img src="<?= IMAGES_URL ?>/products/<?= htmlspecialchars($item['image'] ?? 'default.png') ?>"
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     class="cart-item-image me-3">

                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                    <p class="text-primary mb-0">
                                        <?php
                                        $unitPrice = $item['sale_price'] ?: $item['price'];
                                        echo formatPrice($unitPrice);
                                        ?>
                                    </p>
                                </div>

                                <div class="quantity-control d-flex align-items-center me-4">
                                    <button type="button" class="btn btn-outline-secondary btn-sm quantity-btn" data-action="decrease">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number" class="form-control cart-quantity mx-2"
                                           value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
                                    <button type="button" class="btn btn-outline-secondary btn-sm quantity-btn" data-action="increase">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>

                                <div class="text-end me-3" style="min-width: 80px;">
                                    <strong class="item-total">
                                        <?= formatPrice($unitPrice * $item['quantity']) ?>
                                    </strong>
                                </div>

                                <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i>Vazhdo Blerjen
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Përmbledhja</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Nëntotali:</span>
                            <span id="subtotal"><?= formatPrice($subtotal) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Transporti:</span>
                            <span id="shipping">
                                <?php if ($shipping === 0): ?>
                                    <span class="text-success">Falas</span>
                                <?php else: ?>
                                    <?= formatPrice($shipping) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if ($subtotal < 50): ?>
                            <div class="alert alert-info small mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Shto <?= formatPrice(50 - $subtotal) ?> më shumë për transport falas!
                            </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Totali:</strong>
                            <strong class="text-primary fs-5" id="total"><?= formatPrice($total) ?></strong>
                        </div>

                        <a href="checkout.php" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-credit-card me-1"></i>Vazhdo me Pagesën
                        </a>
                    </div>
                </div>

                <!-- Promo Code -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="mb-3">Kod Promocional</h6>
                        <div class="input-group">
                            <input type="text" class="form-control" id="promoCode" placeholder="Fut kodin">
                            <button class="btn btn-outline-primary" type="button" id="applyPromo">Apliko</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update quantity
    document.querySelectorAll('.cart-quantity').forEach(input => {
        input.addEventListener('change', async function() {
            const cartItem = this.closest('.cart-item');
            const productId = cartItem.dataset.productId;
            const quantity = parseInt(this.value);

            if (quantity < 1) {
                this.value = 1;
                return;
            }

            if (typeof AppUtils !== 'undefined' && AppUtils.updateCartQuantity) {
                await AppUtils.updateCartQuantity(productId, quantity);
                location.reload();
            }
        });
    });

    // Remove item
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', async function() {
            const cartItem = this.closest('.cart-item');
            const productId = cartItem.dataset.productId;

            if (confirm('Jeni të sigurt që doni ta hiqni këtë produkt?')) {
                if (typeof AppUtils !== 'undefined' && AppUtils.removeFromCart) {
                    await AppUtils.removeFromCart(productId);
                }
            }
        });
    });

    // Apply promo code
    const promoBtn = document.getElementById('applyPromo');
    if (promoBtn) {
        promoBtn.addEventListener('click', function() {
            const code = document.getElementById('promoCode').value.trim();
            if (code && typeof AppUtils !== 'undefined') {
                AppUtils.showToast('info', 'Funksionaliteti i kodeve promocionale do të shtohet së shpejti.');
            }
        });
    }
});
</script>
