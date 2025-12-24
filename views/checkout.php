<?php
/**
 * FAQJA E CHECKOUT
 * =================
 * Procesimi i porosisë dhe pagesës.
 */

$pageTitle = 'Checkout - ' . SITE_NAME;
require_once __DIR__ . '/partials/header.php';

// Kontrollo login
if (!isLoggedIn()) {
    setFlash('warning', 'Duhet të logohesh për të bërë porosi.');
    redirect('views/auth/login.php?redirect=checkout.php');
}

$productObj = new Product();
$paymentObj = new Payment();
$userId = getCurrentUserId();

// Merr shportën
$cart = $productObj->getCart($userId);
if (empty($cart)) {
    setFlash('warning', 'Shporta juaj është bosh.');
    redirect('views/cart.php');
}

// Merr të dhënat e përdoruesit
$userObj = new User();
$userData = $userObj->getUser($userId);

// Llogarit totalet
$subtotal = $productObj->getCartTotal($userId);
$tax = $subtotal * 0.20;
$shipping = $subtotal > 50 ? 0 : 5;
$total = $subtotal + $tax + $shipping;

$errors = [];
$orderId = null;

// Proceso porosinë
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesioni ka skaduar.';
    } else {
        // Valido të dhënat
        $shippingAddress = sanitize($_POST['shipping_address'] ?? '');
        $shippingCity = sanitize($_POST['shipping_city'] ?? '');
        $shippingPhone = sanitize($_POST['shipping_phone'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? 'stripe';
        $notes = sanitize($_POST['notes'] ?? '');

        if (empty($shippingAddress)) $errors[] = 'Adresa është e detyrueshme';
        if (empty($shippingCity)) $errors[] = 'Qyteti është i detyrueshëm';
        if (empty($shippingPhone)) $errors[] = 'Telefoni është i detyrueshëm';

        if (empty($errors)) {
            // Krijo porosinë
            $orderResult = $productObj->createOrder($userId, [
                'address' => $shippingAddress,
                'city' => $shippingCity,
                'phone' => $shippingPhone,
                'notes' => $notes
            ]);

            if ($orderResult['success']) {
                $orderId = $orderResult['order_id'];

                // Processo pagesën sipas metodës
                if ($paymentMethod === 'paypal') {
                    $paypalResult = $paymentObj->createPayPalOrder($orderResult['total'], $orderId);
                    if ($paypalResult['success']) {
                        // Ruaj pagesën dhe ridrejto te PayPal
                        $paymentObj->savePayment($orderId, $userId, 'paypal', $paypalResult['paypal_order_id'], $orderResult['total']);
                        redirect($paypalResult['approve_url']);
                    } else {
                        $errors[] = $paypalResult['message'];
                    }
                } elseif ($paymentMethod === 'bank_transfer') {
                    // Për transfertë bankare
                    $paymentObj->savePayment($orderId, $userId, 'bank_transfer', 'BT-' . $orderId, $orderResult['total']);
                    setFlash('success', 'Porosia u regjistrua! Do të proceshohet pas marrjes së pagesës.');
                    redirect('views/checkout-success.php?order_id=' . $orderId . '&method=bank');
                } else {
                    // Stripe - ridrejto te faqja e pagesës
                    $paymentObj->savePayment($orderId, $userId, 'stripe', 'pending', $orderResult['total']);
                    redirect('views/stripe-checkout.php?order_id=' . $orderId);
                }
            } else {
                $errors[] = $orderResult['message'];
            }
        }
    }
}
?>

<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-credit-card me-2"></i>Checkout</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?= implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="checkoutForm">
        <?= csrfField() ?>

        <div class="row g-4">
            <!-- Shipping & Payment Form -->
            <div class="col-lg-8">
                <!-- Shipping Information -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Adresa e Dërgesës</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Emri *</label>
                                <input type="text"
                                       class="form-control"
                                       value="<?= htmlspecialchars($userData['first_name']) ?>"
                                       readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mbiemri *</label>
                                <input type="text"
                                       class="form-control"
                                       value="<?= htmlspecialchars($userData['last_name']) ?>"
                                       readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Adresa *</label>
                                <input type="text"
                                       class="form-control"
                                       name="shipping_address"
                                       value="<?= htmlspecialchars($userData['address'] ?? '') ?>"
                                       placeholder="Rruga, numri, pallati, apartamenti"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Qyteti *</label>
                                <input type="text"
                                       class="form-control"
                                       name="shipping_city"
                                       value="<?= htmlspecialchars($userData['city'] ?? '') ?>"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefoni *</label>
                                <input type="tel"
                                       class="form-control"
                                       name="shipping_phone"
                                       value="<?= htmlspecialchars($userData['phone'] ?? '') ?>"
                                       placeholder="+355 69 123 4567"
                                       required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Shënime (opsionale)</label>
                                <textarea class="form-control"
                                          name="notes"
                                          rows="2"
                                          placeholder="Udhëzime për dërgesën, orari i preferuar, etj."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Metoda e Pagesës</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            <!-- Stripe -->
                            <label class="payment-method p-3 border rounded cursor-pointer">
                                <div class="d-flex align-items-center">
                                    <input type="radio"
                                           name="payment_method"
                                           value="stripe"
                                           class="form-check-input me-3"
                                           checked>
                                    <div class="flex-grow-1">
                                        <strong>Kartë Krediti / Debiti</strong>
                                        <p class="text-muted small mb-0">Visa, Mastercard, American Express</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-credit-card-2-front fs-3 text-primary"></i>
                                    </div>
                                </div>
                            </label>

                            <!-- PayPal -->
                            <label class="payment-method p-3 border rounded cursor-pointer">
                                <div class="d-flex align-items-center">
                                    <input type="radio"
                                           name="payment_method"
                                           value="paypal"
                                           class="form-check-input me-3">
                                    <div class="flex-grow-1">
                                        <strong>PayPal</strong>
                                        <p class="text-muted small mb-0">Paguaj me llogarinë tënde PayPal</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-paypal fs-3" style="color: #003087;"></i>
                                    </div>
                                </div>
                            </label>

                            <!-- Bank Transfer -->
                            <label class="payment-method p-3 border rounded cursor-pointer">
                                <div class="d-flex align-items-center">
                                    <input type="radio"
                                           name="payment_method"
                                           value="bank_transfer"
                                           class="form-check-input me-3">
                                    <div class="flex-grow-1">
                                        <strong>Transfertë Bankare</strong>
                                        <p class="text-muted small mb-0">Paguaj direkt në llogarinë tonë bankare</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-bank fs-3 text-secondary"></i>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Bank Details (shown when bank_transfer selected) -->
                        <div id="bankDetails" class="alert alert-info mt-3" style="display: none;">
                            <h6><i class="bi bi-info-circle me-2"></i>Detajet Bankare:</h6>
                            <p class="mb-1"><strong>Banka:</strong> Banka Kombëtare</p>
                            <p class="mb-1"><strong>IBAN:</strong> AL00 0000 0000 0000 0000 0000 0000</p>
                            <p class="mb-1"><strong>Përfituesi:</strong> Web Platform SH.P.K</p>
                            <p class="mb-0"><strong>Përshkrimi:</strong> Porosia #[do të gjenerohet]</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 80px;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-bag me-2"></i>Përmbledhja e Porosisë</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cart Items -->
                        <?php foreach ($cart as $item): ?>
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?= SITE_URL ?>/assets/images/uploads/<?= $item['image'] ?? 'products/default.png' ?>"
                                     class="rounded me-2"
                                     width="50" height="50"
                                     style="object-fit: cover;">
                                <div class="flex-grow-1">
                                    <small class="d-block"><?= htmlspecialchars($item['name']) ?></small>
                                    <small class="text-muted">x<?= $item['quantity'] ?></small>
                                </div>
                                <span><?= formatPrice(($item['sale_price'] ?? $item['price']) * $item['quantity']) ?></span>
                            </div>
                        <?php endforeach; ?>

                        <hr>

                        <!-- Totals -->
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
                            <span class="<?= $shipping === 0 ? 'text-success' : '' ?>">
                                <?= $shipping === 0 ? 'Falas' : formatPrice($shipping) ?>
                            </span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Totali:</strong>
                            <strong class="text-primary fs-5"><?= formatPrice($total) ?></strong>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="bi bi-lock me-2"></i>Përfundo Porosinë
                        </button>

                        <p class="text-muted small text-center mt-3 mb-0">
                            <i class="bi bi-shield-check me-1"></i>
                            Pagesa juaj është e sigurt dhe e enkriptuar
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Show/hide bank details
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('bankDetails').style.display =
            this.value === 'bank_transfer' ? 'block' : 'none';
    });
});

// Highlight selected payment method
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('border-primary'));
        this.classList.add('border-primary');
    });
});
</script>

<style>
.payment-method {
    transition: all 0.2s;
}
.payment-method:hover {
    border-color: var(--bs-primary) !important;
    background-color: #f8f9fa;
}
.payment-method.border-primary {
    border-width: 2px !important;
}
</style>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
