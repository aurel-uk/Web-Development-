-- ============================================
-- SKEMA E DATABAZËS - WEB PLATFORM (Docker)
-- ============================================
-- Ekzekutohet automatikisht kur MySQL container niset për herë të parë.

-- Përdor databazën (krijohet automatikisht nga docker-compose)
USE web_platform;

-- ============================================
-- TABELA: ROLET
-- ============================================
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO roles (name, description) VALUES
('user', 'Përdorues i zakonshëm i platformës'),
('admin', 'Administrator me të drejta të plota'),
('moderator', 'Moderator me të drejta të kufizuara')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- ============================================
-- TABELA: PËRDORUESIT
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role_id INT DEFAULT 1,
    email_verified BOOLEAN DEFAULT FALSE,
    two_factor_enabled BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    avatar VARCHAR(255) DEFAULT 'default.png',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_users_email ON users(email);

-- ============================================
-- TABELA: VERIFIKIMI I EMAIL-IT
-- ============================================
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABELA: PASSWORD RESETS
-- ============================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABELA: TENTATIVAT E LOGIN
-- ============================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    attempts INT DEFAULT 1,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_login_email ON login_attempts(email);

-- ============================================
-- TABELA: REMEMBER ME TOKENS
-- ============================================
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABELA: LOGET E PËRDORUESVE
-- ============================================
CREATE TABLE IF NOT EXISTS user_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_logs_user ON user_logs(user_id);
CREATE INDEX idx_logs_action ON user_logs(action);

-- ============================================
-- TABELA: KATEGORITË
-- ============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    parent_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABELA: PRODUKTET
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2),
    stock INT DEFAULT 0,
    image VARCHAR(255) DEFAULT 'default.png',
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_products_category ON products(category_id);

-- ============================================
-- TABELA: SHPORTA (CART)
-- ============================================
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
) ENGINE=InnoDB;

-- ============================================
-- TABELA: POROSITË
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    subtotal DECIMAL(10, 2) NOT NULL,
    shipping_cost DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'cash',
    shipping_first_name VARCHAR(50),
    shipping_last_name VARCHAR(50),
    shipping_email VARCHAR(100),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_postal_code VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);

-- ============================================
-- TABELA: ARTIKUJT E POROSIVE
-- ============================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABELA: TWO-FACTOR AUTHENTICATION (2FA)
-- ============================================
CREATE TABLE IF NOT EXISTS two_factor_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_2fa_user ON two_factor_codes(user_id);
CREATE INDEX idx_2fa_code ON two_factor_codes(code);

-- ============================================
-- TABELA: MESAZHET E KONTAKTIT
-- ============================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(100),
    message TEXT NOT NULL,
    ip_address VARCHAR(45),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TË DHËNA SHEMBULL
-- ============================================

-- Admin default (Fjalëkalimi: Admin123!)
-- Hash: password_hash('Admin123!', PASSWORD_DEFAULT)
INSERT INTO users (first_name, last_name, email, password, role_id, email_verified) VALUES
('Admin', 'System', 'admin@webplatform.com', '$2y$10$xPIF1gMxqOYxqiGNPxkGKuVrOYZ5hV7cHH6pLx1h0kLVR9qXiG5Ky', 2, TRUE)
ON DUPLICATE KEY UPDATE first_name = VALUES(first_name);

-- Përdorues test (Fjalëkalimi: Test123!)
INSERT INTO users (first_name, last_name, email, password, role_id, email_verified) VALUES
('Test', 'User', 'test@webplatform.com', '$2y$10$xPIF1gMxqOYxqiGNPxkGKuVrOYZ5hV7cHH6pLx1h0kLVR9qXiG5Ky', 1, TRUE)
ON DUPLICATE KEY UPDATE first_name = VALUES(first_name);

-- Kategori shembull
INSERT INTO categories (name, slug, description) VALUES
('Elektronikë', 'elektronike', 'Pajisje elektronike dhe aksesorë'),
('Veshje', 'veshje', 'Veshje për meshkuj dhe femra'),
('Shtëpi', 'shtepi', 'Artikuj për shtëpinë'),
('Sport', 'sport', 'Artikuj sportive'),
('Libra', 'libra', 'Libra dhe revista'),
('Kozmetikë', 'kozmetike', 'Produkte kozmetike dhe kujdesi')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Produkte shembull
INSERT INTO products (category_id, name, slug, description, price, sale_price, stock, is_active) VALUES
(1, 'Smartphone XYZ Pro', 'smartphone-xyz-pro', 'Telefon i mençur me ekran 6.7 inch AMOLED, 128GB', 349.99, 299.99, 50, TRUE),
(1, 'Laptop Pro 15', 'laptop-pro-15', 'Laptop profesional 15.6 inch, Intel i7, 16GB RAM, 512GB SSD', 899.99, NULL, 20, TRUE),
(1, 'Kufje Wireless', 'kufje-wireless', 'Kufje bluetooth me noise cancellation', 79.99, 59.99, 100, TRUE),
(2, 'Xhaketë Dimri Premium', 'xhakete-dimri-premium', 'Xhaketë e ngrohtë waterproof për dimër', 129.99, 99.99, 75, TRUE),
(2, 'Bluza Sportive', 'bluza-sportive', 'Bluza e lehtë për stërvitje dhe vrapim', 39.99, NULL, 150, TRUE),
(3, 'Tavolinë Kafeje Moderne', 'tavoline-kafeje-moderne', 'Tavolinë elegante për dhomën e ndenjës', 189.99, 149.99, 30, TRUE),
(3, 'Llambë LED Smart', 'llambe-led-smart', 'Llambë e kontrolluar me WiFi dhe app', 29.99, NULL, 200, TRUE),
(4, 'Top Futbolli Pro', 'top-futbolli-pro', 'Top futbolli profesional FIFA approved', 49.99, 39.99, 100, TRUE),
(4, 'Pesha Fitness Set', 'pesha-fitness-set', 'Set peshash 2-10kg për stërvitje në shtëpi', 89.99, NULL, 40, TRUE),
(5, 'Koleksion Libra Programimi', 'koleksion-libra-programimi', 'Set me 5 libra për të mësuar programim', 59.99, 49.99, 60, TRUE)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Mesazh i parë log
INSERT INTO user_logs (user_id, action, description, ip_address) VALUES
(1, 'system_init', 'Databaza u inicializua me sukses', '127.0.0.1');

SELECT 'Databaza u krijua dhe u popullu me sukses!' AS message;
