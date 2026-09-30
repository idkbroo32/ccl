-- Ranchi Mart / Neon PostgreSQL schema
-- This file is executed automatically by backend/db.php.

CREATE TABLE IF NOT EXISTS categories (
    id BIGSERIAL PRIMARY KEY,
    name TEXT NOT NULL UNIQUE,
    slug TEXT NOT NULL UNIQUE,
    image TEXT
);

CREATE TABLE IF NOT EXISTS products (
    id BIGSERIAL PRIMARY KEY,
    category_id BIGINT NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    price NUMERIC(12,2) NOT NULL DEFAULT 0,
    image TEXT,
    description TEXT,
    stock INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    phone TEXT,
    address TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id BIGSERIAL PRIMARY KEY,
    order_code TEXT NOT NULL UNIQUE,
    user_email TEXT,
    customer_name TEXT NOT NULL,
    customer_phone TEXT NOT NULL,
    delivery_address TEXT NOT NULL,
    items_json TEXT NOT NULL,
    total_amount NUMERIC(12,2) NOT NULL DEFAULT 0,
    payment_method TEXT NOT NULL DEFAULT 'cod',
    payment_status TEXT NOT NULL DEFAULT 'Pending',
    status TEXT NOT NULL DEFAULT 'Confirmed',
    origin_hub_code TEXT DEFAULT 'HUB-MAIN',
    tracking_notes TEXT DEFAULT 'Order placed & awaiting dispatch from local hub',
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    id BIGSERIAL PRIMARY KEY,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    name TEXT NOT NULL,
    role TEXT NOT NULL,
    phone TEXT,
    avatar TEXT,
    hub_code TEXT,
    hub_name TEXT,
    hub_short TEXT,
    hub_address TEXT,
    hub_landmark TEXT,
    pincodes TEXT,
    delivery_speed TEXT DEFAULT '20 - 35 Mins',
    rating NUMERIC(3,1) DEFAULT 4.9,
    orders_completed TEXT DEFAULT '100+',
    timing TEXT DEFAULT '8:00 AM - 10:00 PM',
    status TEXT DEFAULT 'Open Now',
    badge TEXT DEFAULT 'Verified Hub Manager',
    badge_color TEXT DEFAULT '#16a34a',
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admin_logs (
    id BIGSERIAL PRIMARY KEY,
    admin_id BIGINT,
    admin_username TEXT NOT NULL,
    action TEXT NOT NULL,
    details TEXT,
    entity_type TEXT,
    entity_id BIGINT,
    ip_address TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS product_reviews (
    id BIGSERIAL PRIMARY KEY,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    user_name TEXT NOT NULL,
    rating INTEGER NOT NULL DEFAULT 5,
    review_title TEXT,
    review_text TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_inquiries (
    id BIGSERIAL PRIMARY KEY,
    customer_name TEXT,
    name TEXT,
    email TEXT NOT NULL,
    phone TEXT,
    subject TEXT,
    message TEXT NOT NULL,
    status TEXT DEFAULT 'Unread',
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_orders_code ON orders(order_code);
CREATE INDEX IF NOT EXISTS idx_orders_user ON orders(user_email);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_hub ON orders(origin_hub_code);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_admins_username ON admins(username);
CREATE INDEX IF NOT EXISTS idx_admins_email ON admins(email);
CREATE INDEX IF NOT EXISTS idx_admins_hub ON admins(hub_code);
CREATE INDEX IF NOT EXISTS idx_reviews_product ON product_reviews(product_id);
CREATE INDEX IF NOT EXISTS idx_logs_admin ON admin_logs(admin_username);

INSERT INTO categories (id, name, slug, image) VALUES
(1, 'Health and personal care', 'health-personal-care', 'photos/box1__image.png'),
(2, 'BeautyPicks', 'beautypicks', 'photos/box2_image.png'),
(3, 'Electronics', 'electronics', 'photos/box3_image.png'),
(4, 'Clothes', 'clothes', 'photos/box4_image.png'),
(5, 'Furniture', 'furniture', 'photos/box5_image.png'),
(6, 'Hair Accessories', 'hair-accessories', 'photos/box6_images.png'),
(7, 'Home & Kitchen', 'home-kitchen', 'photos/box9_images.png'),
(8, 'Books & Stationary', 'books-stationary', 'photos/box10_images.png')
ON CONFLICT (id) DO NOTHING;

INSERT INTO products (id, category_id, name, price, image, description, stock) VALUES
(1, 1, 'Vitamin C Boost', 499.00, 'photos/box1__image.png', 'Daily health support for immunity and energy.', 25),
(2, 2, 'Glow Essentials Kit', 899.00, 'photos/box2_image.png', 'Skin-first beauty essentials for everyday care.', 18),
(3, 3, 'Smart Wireless Speaker', 1499.00, 'photos/box3_image.png', 'Compact sound with rich bass and Bluetooth pairing.', 30),
(4, 4, 'Classic Cotton Tee', 599.00, 'photos/box4_image.png', 'Soft cotton comfort with a clean modern fit.', 40),
(5, 5, 'Compact Study Desk', 2499.00, 'photos/box5_image.png', 'Minimal desk designed for modern home workspaces.', 12),
(6, 6, 'Silk Hair Care Set', 799.00, 'photos/box6_images.png', 'Complete hair nourishment for healthy shine.', 22),
(7, 7, 'Cookware Starter Pack', 1999.00, 'photos/box9_images.png', 'Essential kitchen tools for everyday cooking.', 16),
(8, 8, 'Creative Notebook Bundle', 699.00, 'photos/box10_images.png', 'A mix of study essentials for the work-from-home routine.', 28)
ON CONFLICT (id) DO NOTHING;

-- Keep sequences above seeded IDs so future inserts do not collide.
SELECT setval(pg_get_serial_sequence('categories','id'), COALESCE((SELECT MAX(id) FROM categories), 1), true);
SELECT setval(pg_get_serial_sequence('products','id'), COALESCE((SELECT MAX(id) FROM products), 1), true);
