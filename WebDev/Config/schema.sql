-- ============================================

-- SKEMA E DATABAZËS - WEB PLATFORM

-- ============================================

-- Ky skedar krijon të gjitha tabelat e nevojshme.

-- Ekzekutohet në phpMyAdmin ose terminal MySQL.

--

-- SI TË EKZEKUTOHET:

-- 1. Hap XAMPP dhe ndiz MySQL

-- 2. Shko në http://localhost/phpmyadmin

-- 3. Krijo databazë të re me emrin 'web_platform'

-- 4. Shko te tab "SQL" dhe kopjo-ngjit këtë kod

 

-- Krijo databazën (nëse nuk ekziston)

CREATE DATABASE IF NOT EXISTS web_platform

CHARACTER SET utf8mb4

COLLATE utf8mb4_unicode_ci;

 

USE web_platform;

 

-- ============================================

-- TABELA 1: ROLET

-- ============================================

-- Ruan rolet e sistemit (user, admin)

CREATE TABLE IF NOT EXISTS roles (

    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(50) NOT NULL UNIQUE,

    description VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB;

 

-- Shto rolet default

INSERT INTO roles (name, description) VALUES

('user', 'Përdorues i zakonshëm i platformës'),

('admin', 'Administrator me të drejta të plota');

 

-- ============================================

-- TABELA 2: PËRDORUESIT

-- ============================================

-- Tabela kryesore e përdoruesve

CREATE TABLE IF NOT EXISTS users (

    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Të dhënat bazë

    first_name VARCHAR(50) NOT NULL,

    last_name VARCHAR(50) NOT NULL,

    email VARCHAR(100) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,  -- Fjalëkalimi i hashuar

 

    -- Roli dhe statusi

    role_id INT DEFAULT 1,  -- Default: user

    is_verified BOOLEAN DEFAULT FALSE,

    is_active BOOLEAN DEFAULT TRUE,

 

    -- Profili

    phone VARCHAR(20),

    address TEXT,

    city VARCHAR(100),

    profile_image VARCHAR(255) DEFAULT 'default.png',

    bio TEXT,

 

    -- Metadata

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    last_login TIMESTAMP NULL,

 

    -- Lidhja me tabelën e roleve

    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL

) ENGINE=InnoDB;

 

-- Index për kërkim më të shpejtë

CREATE INDEX idx_users_email ON users(email);

CREATE INDEX idx_users_role ON users(role_id);

 

-- ============================================

-- TABELA 3: KODET E VERIFIKIMIT (Email)

-- ============================================

-- Ruan kodet që dërgohen me email për verifikim

CREATE TABLE IF NOT EXISTS verification_codes (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    code VARCHAR(64) NOT NULL,

    type ENUM('email_verify', 'password_reset', '2fa') DEFAULT 'email_verify',

    expires_at TIMESTAMP NOT NULL,

    used_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

 

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

) ENGINE=InnoDB;

 

-- ============================================

-- TABELA 4: TENTATIVAT E LOGIN

-- ============================================

-- Ruan tentativat e dështuara për login

-- Përdoret për bllokimin pas 7 tentativash

CREATE TABLE IF NOT EXISTS login_attempts (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NULL,  -- NULL nëse email nuk ekziston

    email VARCHAR(100) NOT NULL,

    ip_address VARCHAR(45) NOT NULL,

    user_agent TEXT,

    success BOOLEAN DEFAULT FALSE,

    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

 

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

) ENGINE=InnoDB;

 

-- Index për kërkim sipas email dhe IP

CREATE INDEX idx_login_email ON login_attempts(email);

CREATE INDEX idx_login_ip ON login_attempts(ip_address);

 

-- ============================================

-- TABELA 5: REMEMBER ME TOKENS

-- ============================================

-- Ruan tokens për funksionalitetin "Më mbaj mend"

CREATE TABLE IF NOT EXISTS remember_tokens (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    token VARCHAR(255) NOT NULL,  -- Token i hashuar

    expires_at TIMESTAMP NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

 

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

) ENGINE=InnoDB;

 

CREATE INDEX idx_remember_token ON remember_tokens(token);

 

-- ============================================

-- TABELA 6: SESIONET

-- ============================================

-- Ruan sesionet aktive të përdoruesve

CREATE TABLE IF NOT EXISTS sessions (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    session_id VARCHAR(128) NOT NULL UNIQUE,

    ip_address VARCHAR(45),

    user_agent TEXT,

    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

 

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

) ENGINE=InnoDB;

 

-- ============================================

-- TABELA 7: LOGET E PËRDORUESVE

-- ============================================

-- Ruan të gjitha veprimet e përdoruesve

CREATE TABLE IF NOT EXISTS user_logs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT,

    action VARCHAR(100) NOT NULL,  -- p.sh. 'login', 'logout', 'profile_update'

    description TEXT,

    ip_address VARCHAR(45),

    user_agent TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

 

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL

) ENGINE=InnoDB;

 

CREATE INDEX idx_logs_user ON user_logs(user_id);

CREATE INDEX idx_logs_action ON user_logs(action);

 

-- ============================================

-- TABELA 8: KATEGORITË (Funksionalitet shtesë)

-- ============================================

-- Kategoritë e produkteve/shërbimeve

CREATE TABLE IF NOT EXISTS categories (

    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    slug VARCHAR(100) NOT NULL UNIQUE,

    description TEXT,

    image VARCHAR(255),

    parent_id INT NULL,  -- Për nën-kategori

    is_active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

 

    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL

) ENGINE=InnoDB;

 

-- ============================================

-- TABELA 9: PRODUKTET (Funksionalitet shtesë)

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

    image VARCHAR(255),

    is_active BOOLEAN DEFAULT TRUE,

    created_by INT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

 

    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL

) ENGINE=InnoDB;

 

CREATE INDEX idx_products_category ON products(category_id);

CREATE INDEX idx_products_price ON products(price);

 

-- ============================================

-- TABELA 10: POROSITË (Funksionalitet shtesë)

-- ============================================

CREATE TABLE IF NOT EXISTS orders (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    order_number VARCHAR(50) NOT NULL UNIQUE,

    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',

    subtotal DECIMAL(10, 2) NOT NULL,

    tax DECIMAL(10, 2) DEFAULT 0,

    shipping DECIMAL(10, 2) DEFAULT 0,

    total DECIMAL(10, 2) NOT NULL,

 

    -- Adresa e dërgesës

    shipping_address TEXT,

    shipping_city VARCHAR(100),

    shipping_phone VARCHAR(20),

 

    notes TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

 

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

) ENGINE=InnoDB;

 

CREATE INDEX idx_orders_user ON orders(user_id);

CREATE INDEX idx_orders_status ON orders(status);

 

-- ============================================

-- TABELA 11: ARTIKUJT E POROSIVE

-- ============================================

CREATE TABLE IF NOT EXISTS order_items (

    id INT AUTO_INCREMENT PRIMARY KEY,

    order_id INT NOT NULL,

    product_id INT NOT NULL,

    quantity INT NOT NULL,

    price DECIMAL(10, 2) NOT NULL,  -- Çmimi në momentin e blerjes

    total DECIMAL(10, 2) NOT NULL,

 

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE

) ENGINE=InnoDB;

 

-- ============================================

-- TABELA 12: PAGESAT (Stripe/PayPal)

-- ============================================

CREATE TABLE IF NOT EXISTS payments (

    id INT AUTO_INCREMENT PRIMARY KEY,

    order_id INT NOT NULL,

    user_id INT NOT NULL,

    payment_method ENUM('stripe', 'paypal', 'bank_transfer', 'cash') NOT NULL,

    transaction_id VARCHAR(255),  -- ID nga Stripe/PayPal

    amount DECIMAL(10, 2) NOT NULL,

    currency VARCHAR(3) DEFAULT 'EUR',

    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',

    payment_data JSON,  -- Të dhëna shtesë nga payment gateway

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

 

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

) ENGINE=InnoDB;

 

CREATE INDEX idx_payments_transaction ON payments(transaction_id);

 

-- ============================================

-- TABELA 13: KOMUNIKIMI ME PALË TË TRETA

-- ============================================

-- Loget e komunikimit me Stripe/PayPal

CREATE TABLE IF NOT EXISTS api_logs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    service VARCHAR(50) NOT NULL,  -- 'stripe', 'paypal', etj.

    endpoint VARCHAR(255) NOT NULL,

    method VARCHAR(10) NOT NULL,  -- GET, POST, etj.

    request_data JSON,

    response_data JSON,

    status_code INT,

    user_id INT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

 

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL

) ENGINE=InnoDB;

 

-- ============================================

-- TABELA 14: MESAZHET E KONTAKTIT

-- ============================================

CREATE TABLE IF NOT EXISTS contact_messages (

    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    email VARCHAR(100) NOT NULL,

    subject VARCHAR(255),

    message TEXT NOT NULL,

    is_read BOOLEAN DEFAULT FALSE,

    replied_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB;

 

-- ============================================

-- TABELA 15: SHPORTA (Cart)

-- ============================================

CREATE TABLE IF NOT EXISTS cart (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT,

    session_id VARCHAR(128),  -- Për vizitorë pa llogari

    product_id INT NOT NULL,

    quantity INT DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

 

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE

) ENGINE=InnoDB;

 

-- ============================================

-- SHTO NJË ADMIN DEFAULT (Fjalëkalimi: Admin123!)

-- ============================================

-- Fjalëkalimi është hashuar me password_hash()

INSERT INTO users (first_name, last_name, email, password, role_id, is_verified)

VALUES (

    'Admin',

    'System',

    'admin@webplatform.com',

    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- password

    2,  -- admin role

    TRUE

);

 

-- ============================================

-- PËRFUNDIM

-- ============================================

-- Tani ke 15 tabela të lidhura mes veti!

-- Relacionet (FK) sigurojnë integritetin e të dhënave