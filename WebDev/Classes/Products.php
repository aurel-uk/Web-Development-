<?php

/**

 * KLASA PRODUCT

 * ==============

 * Menaxhon produktet, kategoritë dhe shportën.

 */

 

class Product

{

    private Database $db;

 

    public function __construct()

    {

        $this->db = Database::getInstance();

    }

 

    // ============================================

    // PRODUKTET

    // ============================================

 

    /**

     * Merr të gjitha produktet

     */

    public function getAllProducts(int $page = 1, int $perPage = 12, array $filters = []): array

    {

        $offset = ($page - 1) * $perPage;

        $where = ['p.is_active = 1'];

        $params = [];

 

        // Filtër sipas kategorisë

        if (!empty($filters['category_id'])) {

            $where[] = 'p.category_id = ?';

            $params[] = $filters['category_id'];

        }

 

        // Filtër sipas çmimit

        if (!empty($filters['min_price'])) {

            $where[] = 'p.price >= ?';

            $params[] = $filters['min_price'];

        }

        if (!empty($filters['max_price'])) {

            $where[] = 'p.price <= ?';

            $params[] = $filters['max_price'];

        }

 

        // Kërkim

        if (!empty($filters['search'])) {

            $where[] = '(p.name LIKE ? OR p.description LIKE ?)';

            $searchTerm = '%' . $filters['search'] . '%';

            $params[] = $searchTerm;

            $params[] = $searchTerm;

        }

 

        $whereClause = implode(' AND ', $where);

 

        // Renditja

        $orderBy = match ($filters['sort'] ?? 'newest') {

            'price_asc' => 'p.price ASC',

            'price_desc' => 'p.price DESC',

            'name' => 'p.name ASC',

            default => 'p.created_at DESC'

        };

 

        $products = $this->db->fetchAll(

            "SELECT p.*, c.name as category_name FROM products p

             LEFT JOIN categories c ON p.category_id = c.id

             WHERE {$whereClause}

             ORDER BY {$orderBy}

             LIMIT {$perPage} OFFSET {$offset}",

            $params

        );

 

        $total = $this->db->count('products p', $whereClause, $params);

 

        return [

            'products' => $products,

            'total' => $total,

            'pages' => ceil($total / $perPage),

            'current_page' => $page

        ];

    }

 

    /**

     * Merr një produkt me ID

     */

    public function getProduct(int $id): ?array

    {

        return $this->db->fetchOne(

            "SELECT p.*, c.name as category_name FROM products p

             LEFT JOIN categories c ON p.category_id = c.id

             WHERE p.id = ?",

            [$id]

        );

    }

 

    /**

     * Merr produkt me slug

     */

    public function getProductBySlug(string $slug): ?array

    {

        return $this->db->fetchOne(

            "SELECT p.*, c.name as category_name FROM products p

             LEFT JOIN categories c ON p.category_id = c.id

             WHERE p.slug = ?",

            [$slug]

        );

    }

 

    /**

     * Shton produkt të ri (admin)

     */

    public function createProduct(array $data): array

    {

        try {

            $slug = slugify($data['name']);

 

            // Sigurohu që slug është unik

            $existingSlug = $this->db->count('products', 'slug = ?', [$slug]);

            if ($existingSlug > 0) {

                $slug .= '-' . time();

            }

 

            $productId = $this->db->insert('products', [

                'category_id' => $data['category_id'] ?? null,

                'name' => sanitize($data['name']),

                'slug' => $slug,

                'description' => sanitize($data['description'] ?? ''),

                'price' => (float)$data['price'],

                'sale_price' => !empty($data['sale_price']) ? (float)$data['sale_price'] : null,

                'stock' => (int)($data['stock'] ?? 0),

                'image' => $data['image'] ?? null,

                'is_active' => (bool)($data['is_active'] ?? true),

                'created_by' => getCurrentUserId()

            ]);

 

            return ['success' => true, 'product_id' => $productId, 'message' => 'Produkti u shtua'];

 

        } catch (Exception $e) {

            return ['success' => false, 'message' => 'Gabim: ' . $e->getMessage()];

        }

    }

 

    /**

     * Përditëson produkt (admin)

     */

    public function updateProduct(int $id, array $data): array

    {

        try {

            $updateData = [];

 

            if (isset($data['name'])) {

                $updateData['name'] = sanitize($data['name']);

                $updateData['slug'] = slugify($data['name']) . '-' . $id;

            }

            if (isset($data['description'])) {

                $updateData['description'] = sanitize($data['description']);

            }

            if (isset($data['price'])) {

                $updateData['price'] = (float)$data['price'];

            }

            if (array_key_exists('sale_price', $data)) {

                $updateData['sale_price'] = !empty($data['sale_price']) ? (float)$data['sale_price'] : null;

            }

            if (isset($data['stock'])) {

                $updateData['stock'] = (int)$data['stock'];

            }

            if (isset($data['category_id'])) {

                $updateData['category_id'] = (int)$data['category_id'];

            }

            if (isset($data['is_active'])) {

                $updateData['is_active'] = (bool)$data['is_active'];

            }

            if (isset($data['image'])) {

                $updateData['image'] = $data['image'];

            }

 

            $this->db->update('products', $updateData, 'id = ?', [$id]);

 

            return ['success' => true, 'message' => 'Produkti u përditësua'];

 

        } catch (Exception $e) {

            return ['success' => false, 'message' => 'Gabim: ' . $e->getMessage()];

        }

    }

 

    /**

     * Fshin produkt (admin)

     */

    public function deleteProduct(int $id): array

    {

        try {

            // Merr produktin për të fshirë imazhin

            $product = $this->getProduct($id);

            if ($product && $product['image']) {

                deleteImage($product['image']);

            }

 

            $this->db->delete('products', 'id = ?', [$id]);

 

            return ['success' => true, 'message' => 'Produkti u fshi'];

 

        } catch (Exception $e) {

            return ['success' => false, 'message' => 'Gabim: ' . $e->getMessage()];

        }

    }

 

    // ============================================

    // KATEGORITË

    // ============================================

 

    /**

     * Merr të gjitha kategoritë

     */

    public function getAllCategories(): array

    {

        return $this->db->fetchAll(

            "SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count

             FROM categories c WHERE c.is_active = 1 ORDER BY c.name"

        );

    }

 

    /**

     * Merr kategori me ID

     */

    public function getCategory(int $id): ?array

    {

        return $this->db->fetchOne("SELECT * FROM categories WHERE id = ?", [$id]);

    }

 

    /**

     * Shton kategori

     */

    public function createCategory(array $data): array

    {

        try {

            $slug = slugify($data['name']);

 

            $categoryId = $this->db->insert('categories', [

                'name' => sanitize($data['name']),

                'slug' => $slug,

                'description' => sanitize($data['description'] ?? ''),

                'image' => $data['image'] ?? null,

                'parent_id' => $data['parent_id'] ?? null,

                'is_active' => true

            ]);

 

            return ['success' => true, 'category_id' => $categoryId];

 

        } catch (Exception $e) {

            return ['success' => false, 'message' => 'Gabim'];

        }

    }

 

    // ============================================

    // SHPORTA (CART)

    // ============================================

 

    /**

     * Merr shportën e përdoruesit

     */

    public function getCart(?int $userId = null, ?string $sessionId = null): array

    {

        $where = $userId ? 'user_id = ?' : 'session_id = ?';

        $param = $userId ?? $sessionId;

 

        return $this->db->fetchAll(

            "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock

             FROM cart c

             JOIN products p ON c.product_id = p.id

             WHERE {$where}",

            [$param]

        );

    }

 

    /**

     * Llogarit totalin e shportës

     */

    public function getCartTotal(?int $userId = null, ?string $sessionId = null): float

    {

        $cart = $this->getCart($userId, $sessionId);

        $total = 0;

 

        foreach ($cart as $item) {

            $price = $item['sale_price'] ?? $item['price'];

            $total += $price * $item['quantity'];

        }

 

        return $total;

    }

 

    /**

     * Numri i artikujve në shportë

     */

    public function getCartCount(?int $userId = null, ?string $sessionId = null): int

    {

        $where = $userId ? 'user_id = ?' : 'session_id = ?';

        $param = $userId ?? $sessionId;

 

        $result = $this->db->fetchOne(

            "SELECT SUM(quantity) as count FROM cart WHERE {$where}",

            [$param]

        );

 

        return (int)($result['count'] ?? 0);

    }

 

    /**

     * Shton në shportë

     */

    public function addToCart(int $productId, int $quantity = 1, ?int $userId = null, ?string $sessionId = null): array

    {

        // Kontrollo produktin

        $product = $this->getProduct($productId);

        if (!$product || !$product['is_active']) {

            return ['success' => false, 'message' => 'Produkti nuk u gjet'];

        }

 

        // Kontrollo stokun

        if ($product['stock'] < $quantity) {

            return ['success' => false, 'message' => 'Stok i pamjaftueshëm'];

        }

 

        // Kontrollo nëse ekziston në shportë

        $where = $userId ? 'user_id = ? AND product_id = ?' : 'session_id = ? AND product_id = ?';

        $params = [$userId ?? $sessionId, $productId];

 

        $existing = $this->db->fetchOne(

            "SELECT * FROM cart WHERE {$where}",

            $params

        );

 

        if ($existing) {

            // Përditëso sasinë

            $newQuantity = $existing['quantity'] + $quantity;

            if ($newQuantity > $product['stock']) {

                return ['success' => false, 'message' => 'Stok i pamjaftueshëm'];

            }

 

            $this->db->update('cart', ['quantity' => $newQuantity], 'id = ?', [$existing['id']]);

        } else {

            // Shto të ri

            $this->db->insert('cart', [

                'user_id' => $userId,

                'session_id' => $sessionId,

                'product_id' => $productId,

                'quantity' => $quantity

            ]);

        }

 

        return ['success' => true, 'message' => 'U shtua në shportë'];

    }

 

    /**

     * Përditëson sasinë

     */

    public function updateCartQuantity(int $cartId, int $quantity): array

    {

        if ($quantity <= 0) {

            return $this->removeFromCart($cartId);

        }

 

        $cartItem = $this->db->fetchOne("SELECT c.*, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ?", [$cartId]);

 

        if (!$cartItem) {

            return ['success' => false, 'message' => 'Artikulli nuk u gjet'];

        }

 

        if ($quantity > $cartItem['stock']) {

            return ['success' => false, 'message' => 'Stok i pamjaftueshëm'];

        }

 

        $this->db->update('cart', ['quantity' => $quantity], 'id = ?', [$cartId]);

 

        return ['success' => true, 'message' => 'Sasia u përditësua'];

    }

 

    /**

     * Heq nga shporta

     */

    public function removeFromCart(int $cartId): array

    {

        $this->db->delete('cart', 'id = ?', [$cartId]);

        return ['success' => true, 'message' => 'U hoq nga shporta'];

    }

 

    /**

     * Pastron shportën

     */

    public function clearCart(?int $userId = null, ?string $sessionId = null): void

    {

        $where = $userId ? 'user_id = ?' : 'session_id = ?';

        $this->db->delete('cart', $where, [$userId ?? $sessionId]);

    }

 

    // ============================================

    // POROSITË

    // ============================================

 

    /**

     * Krijon porosi nga shporta

     */

    public function createOrder(int $userId, array $shippingData): array

    {

        $cart = $this->getCart($userId);

 

        if (empty($cart)) {

            return ['success' => false, 'message' => 'Shporta është bosh'];

        }

 

        try {

            $this->db->beginTransaction();

 

            $subtotal = $this->getCartTotal($userId);

            $tax = $subtotal * 0.20;  // 20% TVSH

            $shipping = $subtotal > 50 ? 0 : 5;  // Transport falas mbi 50€

            $total = $subtotal + $tax + $shipping;

 

            // Krijo porosinë

            $orderId = $this->db->insert('orders', [

                'user_id' => $userId,

                'order_number' => generateOrderNumber(),

                'status' => 'pending',

                'subtotal' => $subtotal,

                'tax' => $tax,

                'shipping' => $shipping,

                'total' => $total,

                'shipping_address' => sanitize($shippingData['address'] ?? ''),

                'shipping_city' => sanitize($shippingData['city'] ?? ''),

                'shipping_phone' => sanitize($shippingData['phone'] ?? ''),

                'notes' => sanitize($shippingData['notes'] ?? '')

            ]);

 

            // Shto artikujt e porosisë

            foreach ($cart as $item) {

                $price = $item['sale_price'] ?? $item['price'];

                $itemTotal = $price * $item['quantity'];

 

                $this->db->insert('order_items', [

                    'order_id' => $orderId,

                    'product_id' => $item['product_id'],

                    'quantity' => $item['quantity'],

                    'price' => $price,

                    'total' => $itemTotal

                ]);

 

                // Ul stokun

                $this->db->query(

                    "UPDATE products SET stock = stock - ? WHERE id = ?",

                    [$item['quantity'], $item['product_id']]

                );

            }

 

            // Pastro shportën

            $this->clearCart($userId);

 

            $this->db->commit();

 

            $order = $this->db->fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);

 

            logUserAction($userId, 'order_created', 'Porosi e re: ' . $order['order_number']);

 

            return [

                'success' => true,

                'order_id' => $orderId,

                'order_number' => $order['order_number'],

                'total' => $total,

                'message' => 'Porosia u krye me sukses'

            ];

 

        } catch (Exception $e) {

            $this->db->rollback();

            return ['success' => false, 'message' => 'Gabim në krijimin e porosisë'];

        }

    }

 

    /**

     * Merr porositë e përdoruesit

     */

    public function getUserOrders(int $userId, int $page = 1, int $perPage = 10): array

    {

        $offset = ($page - 1) * $perPage;

 

        $orders = $this->db->fetchAll(

            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",

            [$userId]

        );

 

        $total = $this->db->count('orders', 'user_id = ?', [$userId]);

 

        return [

            'orders' => $orders,

            'total' => $total,

            'pages' => ceil($total / $perPage)

        ];

    }

 

    /**

     * Merr detajet e porosisë

     */

    public function getOrderDetails(int $orderId): ?array

    {

        $order = $this->db->fetchOne(

            "SELECT o.*, u.first_name, u.last_name, u.email FROM orders o

             JOIN users u ON o.user_id = u.id WHERE o.id = ?",

            [$orderId]

        );

 

        if (!$order) return null;

 

        $order['items'] = $this->db->fetchAll(

            "SELECT oi.*, p.name, p.image FROM order_items oi

             JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?",

            [$orderId]

        );

 

        return $order;

    }

 

    /**

     * Përditëson statusin e porosisë (admin)

     */

    public function updateOrderStatus(int $orderId, string $status): array

    {

        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

 

        if (!in_array($status, $validStatuses)) {

            return ['success' => false, 'message' => 'Status i pavlefshëm'];

        }

 

        try {

            $this->db->update('orders', ['status' => $status], 'id = ?', [$orderId]);

 

            logUserAction(getCurrentUserId(), 'order_status_change', "Porosia #{$orderId} → {$status}");

 

            return ['success' => true, 'message' => 'Statusi u përditësua'];

 

        } catch (Exception $e) {

            return ['success' => false, 'message' => 'Gabim'];

        }

    }

}