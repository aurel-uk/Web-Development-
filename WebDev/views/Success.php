<?php

/**

 * FAQJA E SUKSESIT TË CHECKOUT

 * =============================

 * Shfaqet pas pagesës së suksesshme.

 */

 

$pageTitle = 'Porosia u Krye - ' . SITE_NAME;

require_once __DIR__ . '/partials/header.php';

 

if (!isLoggedIn()) {

    redirect('views/auth/login.php');

}

 

$orderId = (int)($_GET['order_id'] ?? 0);

$paymentMethod = $_GET['method'] ?? 'card';

 

$productObj = new Product();

$order = $productObj->getOrderDetails($orderId);

 

// Verifiko që porosia i përket përdoruesit aktual

if (!$order || $order['user_id'] !== getCurrentUserId()) {

    setFlash('error', 'Porosia nuk u gjet.');

    redirect('views/user/dashboard.php');

}

?>

 

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <!-- Success Message -->

            <div class="text-center mb-5">

                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-4 mb-4">

                    <i class="bi bi-check-circle-fill text-success display-3"></i>

                </div>

                <h1 class="fw-bold text-success">Faleminderit për Porosinë!</h1>

                <p class="lead text-muted">

                    Porosia juaj u regjistrua me sukses.

                </p>

            </div>

 

            <!-- Order Details Card -->

            <div class="card shadow-sm mb-4">

                <div class="card-header bg-white">

                    <div class="d-flex justify-content-between align-items-center">

                        <h5 class="mb-0">

                            <i class="bi bi-receipt me-2"></i>Detajet e Porosisë

                        </h5>

                        <span class="badge bg-primary"><?= $order['order_number'] ?></span>

                    </div>

                </div>

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <p class="mb-1"><strong>Data:</strong></p>

                            <p class="text-muted"><?= formatDate($order['created_at']) ?></p>

                        </div>

                        <div class="col-md-6">

                            <p class="mb-1"><strong>Statusi:</strong></p>

                            <span class="badge bg-warning">Në Pritje</span>

                        </div>

                        <div class="col-md-6">

                            <p class="mb-1"><strong>Metoda e Pagesës:</strong></p>

                            <p class="text-muted">

                                <?php

                                echo match($paymentMethod) {

                                    'bank' => '<i class="bi bi-bank me-1"></i> Transfertë Bankare',

                                    'paypal' => '<i class="bi bi-paypal me-1"></i> PayPal',

                                    default => '<i class="bi bi-credit-card me-1"></i> Kartë'

                                };

                                ?>

                            </p>

                        </div>

                        <div class="col-md-6">

                            <p class="mb-1"><strong>Totali:</strong></p>

                            <p class="text-primary fs-5 fw-bold"><?= formatPrice($order['total']) ?></p>

                        </div>

                    </div>

 

                    <?php if ($paymentMethod === 'bank'): ?>

                        <div class="alert alert-info mt-3 mb-0">

                            <h6><i class="bi bi-info-circle me-2"></i>Detajet për Pagesë:</h6>

                            <p class="mb-1"><strong>Banka:</strong> Banka Kombëtare</p>

                            <p class="mb-1"><strong>IBAN:</strong> AL00 0000 0000 0000 0000 0000 0000</p>

                            <p class="mb-1"><strong>Përfituesi:</strong> Web Platform SH.P.K</p>

                            <p class="mb-0"><strong>Përshkrimi:</strong> <?= $order['order_number'] ?></p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

 

            <!-- Order Items -->

            <div class="card shadow-sm mb-4">

                <div class="card-header bg-white">

                    <h5 class="mb-0"><i class="bi bi-bag me-2"></i>Produktet</h5>

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>Produkti</th>

                                    <th>Çmimi</th>

                                    <th>Sasia</th>

                                    <th class="text-end">Total</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($order['items'] as $item): ?>

                                    <tr>

                                        <td>

                                            <div class="d-flex align-items-center">

                                                <img src="<?= SITE_URL ?>/assets/images/uploads/<?= $item['image'] ?? 'products/default.png' ?>"

                                                     class="rounded me-2"

                                                     width="40" height="40"

                                                     style="object-fit: cover;">

                                                <?= htmlspecialchars($item['name']) ?>

                                            </div>

                                        </td>

                                        <td><?= formatPrice($item['price']) ?></td>

                                        <td><?= $item['quantity'] ?></td>

                                        <td class="text-end"><?= formatPrice($item['total']) ?></td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                            <tfoot class="table-light">

                                <tr>

                                    <td colspan="3" class="text-end"><strong>Nën-totali:</strong></td>

                                    <td class="text-end"><?= formatPrice($order['subtotal']) ?></td>

                                </tr>

                                <tr>

                                    <td colspan="3" class="text-end">TVSH (20%):</td>

                                    <td class="text-end"><?= formatPrice($order['tax']) ?></td>

                                </tr>

                                <tr>

                                    <td colspan="3" class="text-end">Transporti:</td>

                                    <td class="text-end"><?= $order['shipping'] == 0 ? 'Falas' : formatPrice($order['shipping']) ?></td>

                                </tr>

                                <tr>

                                    <td colspan="3" class="text-end"><strong>Totali:</strong></td>

                                    <td class="text-end text-primary fw-bold"><?= formatPrice($order['total']) ?></td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                </div>

            </div>

 

            <!-- Shipping Address -->

            <div class="card shadow-sm mb-4">

                <div class="card-header bg-white">

                    <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Adresa e Dërgesës</h5>

                </div>

                <div class="card-body">

                    <p class="mb-1"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></p>

                    <p class="mb-1"><?= htmlspecialchars($order['shipping_address']) ?></p>

                    <p class="mb-1"><?= htmlspecialchars($order['shipping_city']) ?></p>

                    <p class="mb-0"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($order['shipping_phone']) ?></p>

                </div>

            </div>

 

            <!-- Actions -->

            <div class="d-flex justify-content-center gap-3">

                <a href="user/orders.php" class="btn btn-primary">

                    <i class="bi bi-bag me-2"></i>Shiko Porositë

                </a>

                <a href="products.php" class="btn btn-outline-primary">

                    <i class="bi bi-arrow-left me-2"></i>Vazhdo Blerjen

                </a>

            </div>

        </div>

    </div>

</div>

 

<?php require_once __DIR__ . '/partials/footer.php'; ?>
