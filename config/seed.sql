-- Enhanced seed data for the comprehensive e-commerce database

-- Update user passwords with proper hashes (password123 for both users)
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE id = 1;
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE id = 2;

-- Assign roles to users
INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(1, 1), -- Admin user gets admin role
(2, 2); -- Customer user gets customer role

-- Add more sample products with the new schema structure
INSERT IGNORE INTO products (sku, name, slug, description, price, sale_price, stock, is_active) VALUES
('LAPTOP-001', 'Gaming Laptop Pro', 'gaming-laptop-pro', 'High-performance gaming laptop with RTX graphics and 16GB RAM. Perfect for gaming and professional work.', 1299.99, 1199.99, 15, 1),
('PHONE-001', 'Smartphone X1', 'smartphone-x1', 'Latest smartphone with advanced camera system and 5G connectivity. 128GB storage included.', 699.99, NULL, 50, 1),
('HEADSET-001', 'Wireless Gaming Headset', 'wireless-gaming-headset', 'Premium wireless gaming headset with noise cancellation and 7.1 surround sound.', 149.99, 129.99, 30, 1),
('MOUSE-001', 'Ergonomic Wireless Mouse', 'ergonomic-wireless-mouse', 'Comfortable wireless mouse with precision tracking and long battery life.', 49.99, NULL, 75, 1),
('KEYBOARD-001', 'Mechanical Gaming Keyboard', 'mechanical-gaming-keyboard', 'RGB backlit mechanical keyboard with tactile switches and programmable keys.', 89.99, 79.99, 25, 1);

-- Add categories for the products
INSERT IGNORE INTO categories (name, slug, description, parent_id) VALUES
('Electronics', 'electronics', 'Electronic devices and gadgets', NULL),
('Computers', 'computers', 'Laptops, desktops, and computer accessories', 1),
('Mobile Devices', 'mobile-devices', 'Smartphones, tablets, and mobile accessories', 1),
('Gaming', 'gaming', 'Gaming equipment and accessories', NULL),
('Audio', 'audio', 'Headphones, speakers, and audio equipment', 1);

-- New high-level categories
INSERT IGNORE INTO categories (name, slug, description, parent_id) VALUES
('Fashion', 'fashion', 'Clothing, shoes, and accessories', NULL),
('Home & Living', 'home-living', 'Furniture, decor, and household items', NULL),
('Beauty', 'beauty', 'Skincare, haircare, and cosmetics', NULL),
('Accessories', 'accessories', 'Bags, jewelry, watches, and extras', NULL),
('Shoes', 'shoes', 'Footwear for men, women, and kids', NULL);

-- Link products to categories
INSERT IGNORE INTO product_category (product_id, category_id) VALUES
(1, 1), -- Sample Product -> Default
(2, 2), -- Gaming Laptop -> Computers
(2, 4), -- Gaming Laptop -> Gaming
(3, 3), -- Smartphone -> Mobile Devices
(4, 5), -- Gaming Headset -> Audio
(4, 4), -- Gaming Headset -> Gaming
(5, 2), -- Wireless Mouse -> Computers
(6, 2), -- Gaming Keyboard -> Computers
(6, 4); -- Gaming Keyboard -> Gaming

-- Add sample addresses for the customer
INSERT IGNORE INTO addresses (user_id, label, line1, line2, city, state, postal_code, country, is_default) VALUES
(2, 'home', '123 Main Street', 'Apt 4B', 'New York', 'NY', '10001', 'USA', 1),
(2, 'work', '456 Business Ave', 'Suite 200', 'New York', 'NY', '10002', 'USA', 0);

-- Add some sample reviews
INSERT IGNORE INTO reviews (product_id, user_id, rating, title, body, approved) VALUES
(1, 2, 4, 'Good basic product', 'This sample product works well for testing purposes. Good quality for the price.', 1),
(2, 2, 5, 'Excellent gaming laptop!', 'Amazing performance for gaming and work. The RTX graphics are incredible and the build quality is top-notch.', 1),
(3, 2, 4, 'Great phone', 'Love the camera quality and the 5G speed is impressive. Battery life could be better but overall very satisfied.', 1);

-- Add site settings
INSERT IGNORE INTO settings (`key`, `value`) VALUES
('site_name', 'E-Commerce Store'),
('site_description', 'Your one-stop shop for electronics and gaming gear'),
('currency', 'KSh'),
('tax_rate', '0.08'),
('delivery_base_fee', '150'),
('delivery_per_km', '50'),
('free_delivery_threshold', '0')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- Backward-compat: if shipping keys were inserted earlier, keep them in sync for legacy code paths
-- Optional: you may remove shipping_rate/free_shipping_threshold once all code paths use delivery
INSERT IGNORE INTO settings (`key`, `value`) VALUES
('shipping_rate', '0'),
('free_shipping_threshold', '0.00')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
