<?php
/**
 * KLASA PAYMENT
 * ==============
 * Menaxhon integrimet me Stripe dhe PayPal.
 *
 * SHPJEGIM për fillestarët:
 * - Stripe dhe PayPal janë platforma për pagesa online
 * - API Keys: Çelësa sekretë që autorizojnë aplikacionin
 * - Webhook: Njoftim automatik kur ndodh një event (p.sh. pagesë e suksesshme)
 *
 * KONFIGURIMI:
 * 1. Krijo llogari në stripe.com dhe/ose paypal.com
 * 2. Merr API keys nga dashboard
 * 3. Vendosi ato në config/database.php
 */

class Payment
{
    private Database $db;

    // Konfigurime Stripe (zëvendëso me të vërtetat në prodhim)
    private string $stripeSecretKey = 'sk_test_your_stripe_secret_key';
    private string $stripePublicKey = 'pk_test_your_stripe_public_key';

    // Konfigurime PayPal (zëvendëso me të vërtetat në prodhim)
    private string $paypalClientId = 'your_paypal_client_id';
    private string $paypalSecret = 'your_paypal_secret';
    private string $paypalMode = 'sandbox'; // 'sandbox' ose 'live'

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================
    // STRIPE
    // ============================================

    /**
     * Merr çelësin publik të Stripe
     * (përdoret në frontend për Stripe.js)
     */
    public function getStripePublicKey(): string
    {
        return $this->stripePublicKey;
    }

    /**
     * Krijon një Payment Intent për Stripe
     *
     * SHPJEGIM:
     * Payment Intent është objekti që përfaqëson një pagesë në Stripe.
     * Kthen një "client_secret" që përdoret në frontend.
     *
     * @param float $amount - Shuma në EUR
     * @param int $orderId - ID e porosisë
     * @return array
     */
    public function createStripePaymentIntent(float $amount, int $orderId): array
    {
        try {
            // URL e API-së Stripe
            $url = 'https://api.stripe.com/v1/payment_intents';

            // Të dhënat e kërkesës
            $data = [
                'amount' => (int)($amount * 100), // Stripe përdor cents (100 = 1 EUR)
                'currency' => 'eur',
                'metadata' => [
                    'order_id' => $orderId
                ],
                'automatic_payment_methods' => [
                    'enabled' => 'true'
                ]
            ];

            // Bëj kërkesën HTTP
            $response = $this->makeStripeRequest($url, $data);

            if (isset($response['id'])) {
                // Ruaj në databazë
                $this->logApiRequest('stripe', $url, 'POST', $data, $response, 200);

                return [
                    'success' => true,
                    'client_secret' => $response['client_secret'],
                    'payment_intent_id' => $response['id']
                ];
            }

            return ['success' => false, 'message' => $response['error']['message'] ?? 'Gabim i panjohur'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Gabim në Stripe: ' . $e->getMessage()];
        }
    }

    /**
     * Verifikon statusin e Payment Intent
     */
    public function verifyStripePayment(string $paymentIntentId): array
    {
        try {
            $url = "https://api.stripe.com/v1/payment_intents/{$paymentIntentId}";
            $response = $this->makeStripeRequest($url, [], 'GET');

            if ($response['status'] === 'succeeded') {
                return [
                    'success' => true,
                    'status' => 'completed',
                    'transaction_id' => $paymentIntentId,
                    'amount' => $response['amount'] / 100
                ];
            }

            return [
                'success' => false,
                'status' => $response['status'],
                'message' => 'Pagesa nuk është konfirmuar ende'
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Bën kërkesë HTTP te Stripe API
     */
    private function makeStripeRequest(string $url, array $data, string $method = 'POST'): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->stripeSecretKey . ':',
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    // ============================================
    // PAYPAL
    // ============================================

    /**
     * Merr Access Token për PayPal API
     */
    private function getPayPalAccessToken(): ?string
    {
        $baseUrl = $this->paypalMode === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $url = $baseUrl . '/v1/oauth2/token';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_USERPWD => $this->paypalClientId . ':' . $this->paypalSecret,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    /**
     * Krijon një porosi në PayPal
     *
     * @param float $amount
     * @param int $orderId
     * @return array
     */
    public function createPayPalOrder(float $amount, int $orderId): array
    {
        try {
            $accessToken = $this->getPayPalAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'message' => 'Nuk u arrit të merret access token nga PayPal'];
            }

            $baseUrl = $this->paypalMode === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

            $url = $baseUrl . '/v2/checkout/orders';

            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => 'order_' . $orderId,
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => number_format($amount, 2, '.', '')
                        ]
                    ]
                ],
                'application_context' => [
                    'return_url' => SITE_URL . '/views/checkout-success.php?order_id=' . $orderId,
                    'cancel_url' => SITE_URL . '/views/checkout.php?cancelled=1'
                ]
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($orderData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $accessToken
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($response, true);

            // Log the request
            $this->logApiRequest('paypal', $url, 'POST', $orderData, $data, $httpCode);

            if (isset($data['id'])) {
                // Gjej linkun e aprovimit
                $approveLink = '';
                foreach ($data['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approveLink = $link['href'];
                        break;
                    }
                }

                return [
                    'success' => true,
                    'paypal_order_id' => $data['id'],
                    'approve_url' => $approveLink
                ];
            }

            return ['success' => false, 'message' => $data['message'] ?? 'Gabim në krijimin e porosisë PayPal'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Gabim: ' . $e->getMessage()];
        }
    }

    /**
     * Kap (Capture) pagesën PayPal pas aprovimit
     */
    public function capturePayPalOrder(string $paypalOrderId): array
    {
        try {
            $accessToken = $this->getPayPalAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'message' => 'Token error'];
            }

            $baseUrl = $this->paypalMode === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

            $url = $baseUrl . "/v2/checkout/orders/{$paypalOrderId}/capture";

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $accessToken
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($response, true);
            $this->logApiRequest('paypal', $url, 'POST', [], $data, $httpCode);

            if ($data['status'] === 'COMPLETED') {
                $captureId = $data['purchase_units'][0]['payments']['captures'][0]['id'] ?? $paypalOrderId;

                return [
                    'success' => true,
                    'status' => 'completed',
                    'transaction_id' => $captureId,
                    'payer_email' => $data['payer']['email_address'] ?? ''
                ];
            }

            return ['success' => false, 'message' => 'Pagesa nuk u konfirmua'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ============================================
    // MENAXHIMI I PAGESAVE
    // ============================================

    /**
     * Ruaj pagesën në databazë
     */
    public function savePayment(int $orderId, int $userId, string $method, string $transactionId, float $amount, string $status = 'pending'): int
    {
        return $this->db->insert('payments', [
            'order_id' => $orderId,
            'user_id' => $userId,
            'payment_method' => $method,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => $status
        ]);
    }

    /**
     * Përditëso statusin e pagesës
     */
    public function updatePaymentStatus(int $orderId, string $status, ?string $transactionId = null): bool
    {
        $data = ['status' => $status];
        if ($transactionId) {
            $data['transaction_id'] = $transactionId;
        }

        $this->db->update('payments', $data, 'order_id = ?', [$orderId]);

        // Përditëso edhe statusin e porosisë nëse pagesa u krye
        if ($status === 'completed') {
            $this->db->update('orders', ['status' => 'processing'], 'id = ?', [$orderId]);
            logUserAction(getCurrentUserId(), 'payment_completed', "Pagesa për porosinë #{$orderId}");
        }

        return true;
    }

    /**
     * Merr pagesën sipas order ID
     */
    public function getPaymentByOrderId(int $orderId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM payments WHERE order_id = ?",
            [$orderId]
        );
    }

    /**
     * Log API requests për debugging
     */
    private function logApiRequest(string $service, string $endpoint, string $method, array $requestData, array $responseData, int $statusCode): void
    {
        $this->db->insert('api_logs', [
            'service' => $service,
            'endpoint' => $endpoint,
            'method' => $method,
            'request_data' => json_encode($requestData),
            'response_data' => json_encode($responseData),
            'status_code' => $statusCode,
            'user_id' => getCurrentUserId()
        ]);
    }

    /**
     * Proceson pagesën sipas metodës
     */
    public function processPayment(int $orderId, string $method, array $data = []): array
    {
        // Merr porosinë
        $productObj = new Product();
        $order = $productObj->getOrderDetails($orderId);

        if (!$order) {
            return ['success' => false, 'message' => 'Porosia nuk u gjet'];
        }

        switch ($method) {
            case 'stripe':
                return $this->createStripePaymentIntent($order['total'], $orderId);

            case 'paypal':
                return $this->createPayPalOrder($order['total'], $orderId);

            case 'bank_transfer':
                // Për transfertë bankare, ruaj si pending
                $paymentId = $this->savePayment(
                    $orderId,
                    $order['user_id'],
                    'bank_transfer',
                    'BT-' . time(),
                    $order['total'],
                    'pending'
                );
                return [
                    'success' => true,
                    'message' => 'Porosia u regjistrua. Paguaj me transfertë bankare.',
                    'payment_id' => $paymentId
                ];

            default:
                return ['success' => false, 'message' => 'Metodë pagese e pavlefshme'];
        }
    }
}
