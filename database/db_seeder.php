<?php
/**
 * Database Seeder
 * 
 * Seeds exactly 10 high-quality, realistic products for each category (9 categories).
 * Automatically downloads royalty-free images from Unsplash matching each product.
 */

require_once __DIR__ . '/../config/database.php';

// Prevent timeout
set_time_limit(300);

echo "Starting Database Seeder...\n";

try {
    $pdo = getConnection();
    
    // 1. Ensure Furniture category exists
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute(['furniture']);
    $furnitureCategory = $stmt->fetch();
    
    if (!$furnitureCategory) {
        echo "Creating 'Furniture' category...\n";
        $stmt = $pdo->prepare("INSERT INTO categories (id, name, slug, description, status) VALUES (9, 'Furniture', 'furniture', 'Furniture items for your home and office', 1)");
        $stmt->execute();
        echo "Furniture category created.\n";
    } else {
        echo "Furniture category already exists.\n";
    }

    // 2. Download high-quality royalty-free images from Unsplash
    $uploadsDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    $imagesToDownload = [
        // 1. Mobiles
        'iphone_15.jpg' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&auto=format&fit=crop&q=80',
        'galaxy_s24.jpg' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&auto=format&fit=crop&q=80',
        'oneplus_12.jpg' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&auto=format&fit=crop&q=80',
        'pixel_8.jpg' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format&fit=crop&q=80',
        'redmi_note_13.jpg' => 'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=600&auto=format&fit=crop&q=80',
        'realme_12.jpg' => 'https://images.unsplash.com/photo-1598327106026-d9521da673d1?w=600&auto=format&fit=crop&q=80',
        'moto_edge_50.jpg' => 'https://images.unsplash.com/photo-1585060544812-6b45742d762f?w=600&auto=format&fit=crop&q=80',
        'nothing_2a.jpg' => 'https://images.unsplash.com/photo-1616348436168-de43ad0db179?w=600&auto=format&fit=crop&q=80',
        'vivo_v30.jpg' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&auto=format&fit=crop&q=80',
        'poco_x6.jpg' => 'https://images.unsplash.com/photo-1546054454-aa26e2b734c7?w=600&auto=format&fit=crop&q=80',

        // 2. Laptops
        'macbook_air_m3.jpg' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&auto=format&fit=crop&q=80',
        'dell_xps_13.jpg' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&auto=format&fit=crop&q=80',
        'hp_spectre.jpg' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop&q=80',
        'thinkpad_x1.jpg' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=600&auto=format&fit=crop&q=80',
        'rog_g14.jpg' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=600&auto=format&fit=crop&q=80',
        'predator_helios.jpg' => 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=600&auto=format&fit=crop&q=80',
        'msi_prestige.jpg' => 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=600&auto=format&fit=crop&q=80',
        'surface_5.jpg' => 'https://images.unsplash.com/photo-1589561084283-930aa7b1ce50?w=600&auto=format&fit=crop&q=80',
        'lg_gram.jpg' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop&q=80',
        'razer_blade.jpg' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=600&auto=format&fit=crop&q=80',

        // 3. Fashion
        'denim_jeans.jpg' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=600&auto=format&fit=crop&q=80',
        'casual_tshirt.jpg' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80',
        'leather_jacket.jpg' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&auto=format&fit=crop&q=80',
        'sports_shoes.jpg' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80',
        'formal_shirt.jpg' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&auto=format&fit=crop&q=80',
        'floral_dress.jpg' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=600&auto=format&fit=crop&q=80',
        'hooded_sweatshirt.jpg' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&auto=format&fit=crop&q=80',
        'winter_coat.jpg' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=600&auto=format&fit=crop&q=80',
        'aviators.jpg' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=600&auto=format&fit=crop&q=80',
        'belt_wallet.jpg' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=600&auto=format&fit=crop&q=80',

        // 4. Electronics
        'noise_headphones.jpg' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80',
        'bt_speaker.jpg' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&auto=format&fit=crop&q=80',
        'fitness_band.jpg' => 'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?w=600&auto=format&fit=crop&q=80',
        'action_camera.jpg' => 'https://images.unsplash.com/photo-1502982720700-bfff97f2ecac?w=600&auto=format&fit=crop&q=80',
        'charging_pad.jpg' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80',
        'security_camera.jpg' => 'https://images.unsplash.com/photo-1528319725582-ddc096101511?w=600&auto=format&fit=crop&q=80',
        'power_bank.jpg' => 'https://images.unsplash.com/photo-1585338107529-13afc5f02586?w=600&auto=format&fit=crop&q=80',
        'wireless_mouse.jpg' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=600&auto=format&fit=crop&q=80',
        'usbc_hub.jpg' => 'https://images.unsplash.com/photo-1562408590-e32931084e23?w=600&auto=format&fit=crop&q=80',
        'ring_light.jpg' => 'https://images.unsplash.com/photo-1590608897129-79da98d15969?w=600&auto=format&fit=crop&q=80',

        // 5. Home Decor
        'ceramic_vase.jpg' => 'https://images.unsplash.com/photo-1612196808214-b8e1d6145a8c?w=600&auto=format&fit=crop&q=80',
        'cotton_bedsheet.jpg' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=600&auto=format&fit=crop&q=80',
        'window_curtains.jpg' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=600&auto=format&fit=crop&q=80',
        'desk_lamp.jpg' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600&auto=format&fit=crop&q=80',
        'bath_mat.jpg' => 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=600&auto=format&fit=crop&q=80',
        'scented_candles.jpg' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?w=600&auto=format&fit=crop&q=80',
        'wall_clock.jpg' => 'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?w=600&auto=format&fit=crop&q=80',
        'steel_bottle.jpg' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600&auto=format&fit=crop&q=80',
        'kitchen_knives.jpg' => 'https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?w=600&auto=format&fit=crop&q=80',
        'storage_drawer.jpg' => 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=600&auto=format&fit=crop&q=80',

        // 6. Beauty
        'face_moisturizer.jpg' => 'https://images.unsplash.com/photo-1617897903246-719242758050?w=600&auto=format&fit=crop&q=80',
        'matte_lipstick.jpg' => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=600&auto=format&fit=crop&q=80',
        'charcoal_mask.jpg' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=600&auto=format&fit=crop&q=80',
        'vitamin_c_serum.jpg' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=600&auto=format&fit=crop&q=80',
        'argan_hair_oil.jpg' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=600&auto=format&fit=crop&q=80',
        'mens_perfume.jpg' => 'https://images.unsplash.com/photo-1541643600914-78b084683601?w=600&auto=format&fit=crop&q=80',
        'sunscreen_gel.jpg' => 'https://images.unsplash.com/photo-1598440947619-2c35fc9aa908?w=600&auto=format&fit=crop&q=80',
        'cleansing_facewash.jpg' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&auto=format&fit=crop&q=80',
        'makeup_brushes.jpg' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=600&auto=format&fit=crop&q=80',
        'aloe_vera_gel.jpg' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&auto=format&fit=crop&q=80',

        // 7. Books
        'atomic_habits.jpg' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80',
        'psychology_money.jpg' => 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=600&auto=format&fit=crop&q=80',
        'ikigai_book.jpg' => 'https://images.unsplash.com/photo-1506880018603-83d5b814b5a6?w=600&auto=format&fit=crop&q=80',
        'rich_dad_poor_dad.jpg' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&auto=format&fit=crop&q=80',
        'sapiens_book.jpg' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=600&auto=format&fit=crop&q=80',
        'ends_with_us.jpg' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=600&auto=format&fit=crop&q=80',
        'alchemist_book.jpg' => 'https://images.unsplash.com/photo-1531988042231-d39a9cc12a9a?w=600&auto=format&fit=crop&q=80',
        'thinking_fast_slow.jpg' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600&auto=format&fit=crop&q=80',
        'good_vibes_book.jpg' => 'https://images.unsplash.com/photo-1495640388908-05fa85288e61?w=600&auto=format&fit=crop&q=80',
        'silent_patient.jpg' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=600&auto=format&fit=crop&q=80',

        // 8. Sports
        'cricket_ball.jpg' => 'https://images.unsplash.com/photo-1587280501635-68a0e82cd5ff?w=600&auto=format&fit=crop&q=80',
        'badminton_racket.jpg' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=600&auto=format&fit=crop&q=80',
        'yoga_mat.jpg' => 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=600&auto=format&fit=crop&q=80',
        'gym_dumbbells.jpg' => 'https://images.unsplash.com/photo-1638536532686-d610adfc8e5c?w=600&auto=format&fit=crop&q=80',
        'football_ball.jpg' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=600&auto=format&fit=crop&q=80',
        'swimming_goggles.jpg' => 'https://images.unsplash.com/photo-1551244072-5d12893278ab?w=600&auto=format&fit=crop&q=80',
        'hydration_backpack.jpg' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&auto=format&fit=crop&q=80',
        'skipping_rope.jpg' => 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=600&auto=format&fit=crop&q=80',
        'resistance_bands.jpg' => 'https://images.unsplash.com/photo-1598289431512-b97b0917affc?w=600&auto=format&fit=crop&q=80',
        'sports_bottle.jpg' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600&auto=format&fit=crop&q=80',

        // 9. Furniture
        'coffee_table.jpg' => 'https://images.unsplash.com/photo-1533090161767-e6ffed986c88?w=600&auto=format&fit=crop&q=80',
        'office_chair.jpg' => 'https://images.unsplash.com/photo-1580481072645-022f9a6dbf27?w=600&auto=format&fit=crop&q=80',
        'fabric_sofa.jpg' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600&auto=format&fit=crop&q=80',
        'wood_bed.jpg' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=600&auto=format&fit=crop&q=80',
        'study_desk.jpg' => 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?w=600&auto=format&fit=crop&q=80',
        'tv_cabinet.jpg' => 'https://images.unsplash.com/photo-1595428774223-ef52624120d2?w=600&auto=format&fit=crop&q=80',
        'wood_bookshelf.jpg' => 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=600&auto=format&fit=crop&q=80',
        'chest_drawers.jpg' => 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?w=600&auto=format&fit=crop&q=80',
        'shoe_rack.jpg' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=600&auto=format&fit=crop&q=80',
        'bean_bag.jpg' => 'https://images.unsplash.com/photo-1592078615290-033ee584e267?w=600&auto=format&fit=crop&q=80'
    ];

    echo "Checking/downloading product images...\n";
    foreach ($imagesToDownload as $filename => $url) {
        $destPath = $uploadsDir . $filename;
        if (!file_exists($destPath)) {
            echo "Downloading $filename...\n";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $imgData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $imgData !== false) {
                file_put_contents($destPath, $imgData);
                echo "Successfully saved $filename.\n";
            } else {
                echo "Failed to download $filename (HTTP $httpCode). Copying placeholder...\n";
                @copy($uploadsDir . 'placeholder.png', $destPath);
            }
        } else {
            echo "$filename already exists.\n";
        }
    }

    // 3. Clear existing products
    echo "Clearing existing products...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DELETE FROM products");
    $pdo->exec("ALTER TABLE products AUTO_INCREMENT = 1");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Products table cleared.\n";

    // 4. Products data structure (exactly 10 products per category for 9 categories)
    $categoriesProducts = [
        // 1. Mobiles
        1 => [
            ['iPhone 15 Pro Max', 'Apple', 'Experience titanium design, 5x Telephoto camera, and A17 Pro chip.', 159900, 169900, 150, 4.8, 1240, 'iphone_15.jpg', 1],
            ['Samsung Galaxy S24 Ultra', 'Samsung', 'Galaxy AI is here. Epic camera with 200MP, built-in S Pen, Snapdragon 8 Gen 3.', 129999, 139999, 200, 4.7, 980, 'galaxy_s24.jpg', 1],
            ['OnePlus 12', 'OnePlus', 'Empowered by Snapdragon 8 Gen 3, Trinity Engine, and 4th Gen Hasselblad camera.', 64999, 69999, 100, 4.6, 750, 'oneplus_12.jpg', 0],
            ['Google Pixel 8 Pro', 'Google', 'The all-pro phone engineered by Google. Real-tone camera and custom Tensor G3.', 93999, 106999, 80, 4.5, 430, 'pixel_8.jpg', 0],
            ['Xiaomi Redmi Note 13 Pro', 'Xiaomi', '200MP Camera with OIS, 1.5K 120Hz AMOLED display, 67W turbo charging.', 25999, 29999, 300, 4.3, 1420, 'redmi_note_13.jpg', 0],
            ['Realme 12 Pro+', 'Realme', 'Periscope portrait camera, luxury watch design, Snapdragon 7s Gen 2.', 29999, 34999, 180, 4.2, 560, 'realme_12.jpg', 0],
            ['Motorola Edge 50 Pro', 'Motorola', 'AI-powered camera, 144Hz curved display, 125W turbo power charging.', 31999, 36999, 150, 4.4, 610, 'moto_edge_50.jpg', 0],
            ['Nothing Phone 2a', 'Nothing', 'Unique glyph interface, clean Nothing OS 2.5, Dimensity 7200 Pro processor.', 23999, 25999, 220, 4.4, 820, 'nothing_2a.jpg', 0],
            ['Vivo V30 Pro', 'Vivo', 'Zeiss professional portrait camera, slim design, aura light portrait.', 41999, 46999, 130, 4.5, 410, 'vivo_v30.jpg', 0],
            ['POCO X6 Pro', 'POCO', 'Dimensity 8300-Ultra, WildBoost Optimization 2.0, 120Hz CrystalRes display.', 26999, 30999, 250, 4.3, 1150, 'poco_x6.jpg', 0]
        ],
        // 2. Laptops
        2 => [
            ['MacBook Air M3', 'Apple', 'Supercharged by Apple M3 chip. Thin, light, and with up to 18 hours of battery life.', 114900, 134900, 75, 4.8, 890, 'macbook_air_m3.jpg', 1],
            ['Dell XPS 13', 'Dell', 'Stunning OLED InfinityEdge display, CNC-machined aluminum, Intel Core Ultra 7.', 149990, 169990, 45, 4.6, 310, 'dell_xps_13.jpg', 1],
            ['HP Spectre x360', 'HP', 'Premium 2-in-1 convertible laptop, Intel Evo platform, OLED touch screen.', 139990, 159990, 50, 4.7, 240, 'hp_spectre.jpg', 0],
            ['Lenovo ThinkPad X1 Carbon', 'Lenovo', 'Ultra-light business laptop, legendary durability, Intel Core i7 vPro.', 169990, 189990, 40, 4.6, 180, 'thinkpad_x1.jpg', 0],
            ['ASUS ROG Zephyrus G14', 'ASUS', 'Powerful gaming laptop, AMD Ryzen 9, NVIDIA RTX 4060, Nebula display.', 144990, 164990, 60, 4.7, 450, 'rog_g14.jpg', 0],
            ['Acer Predator Helios 300', 'Acer', 'Intel Core i9, NVIDIA RTX 4070, high-speed 240Hz gaming display.', 129990, 149990, 90, 4.5, 590, 'predator_helios.jpg', 0],
            ['MSI Prestige 14', 'MSI', 'Sleek creator laptop, Intel Core i7, lightweight design, true color display.', 79990, 99990, 80, 4.2, 130, 'msi_prestige.jpg', 0],
            ['Microsoft Surface Laptop 5', 'Microsoft', 'Elegant touch-screen laptop, Omnisonic speakers, all-day battery.', 109990, 129990, 55, 4.4, 210, 'surface_5.jpg', 0],
            ['LG Gram 16', 'LG', 'Under 1.2kg ultra-lightweight laptop, 16-inch WQXGA IPS screen, 80Wh battery.', 99990, 124990, 65, 4.5, 340, 'lg_gram.jpg', 0],
            ['Razer Blade 14', 'Razer', 'Premium aluminum gaming laptop, AMD Ryzen 9, RTX 4070, QHD+ 240Hz screen.', 189990, 209990, 30, 4.6, 120, 'razer_blade.jpg', 0]
        ],
        // 3. Fashion
        3 => [
            ['Slim Fit Denim Jeans', "Levi's", 'Classic 511 slim fit cotton denim jeans with stretch comfort.', 2499, 4999, 450, 4.3, 2450, 'denim_jeans.jpg', 1],
            ['Casual Cotton T-Shirt', 'Puma', 'Breathable casual cotton t-shirt with classic logo print.', 899, 1499, 800, 4.2, 4800, 'casual_tshirt.jpg', 0],
            ['Classic Leather Jacket', 'Zara', 'Premium faux leather biker jacket with metallic zippers.', 4999, 7999, 150, 4.5, 320, 'leather_jacket.jpg', 1],
            ['Running Sports Shoes', 'Nike', 'Lightweight mesh running shoes with foam cushioning.', 5499, 8999, 300, 4.6, 1890, 'sports_shoes.jpg', 0],
            ['Formal Cotton Shirt', 'Raymond', 'Premium pure cotton formal button-up shirt.', 1799, 2999, 400, 4.3, 1120, 'formal_shirt.jpg', 0],
            ['Summer Floral Dress', 'H&M', 'Flowy summer midi dress with beautiful floral pattern print.', 1999, 3499, 250, 4.4, 670, 'floral_dress.jpg', 0],
            ['Hooded Sweatshirt', 'Adidas', 'Warm fleece hooded sweatshirt with kangaroo pockets.', 2799, 3999, 350, 4.4, 980, 'hooded_sweatshirt.jpg', 0],
            ['Woolen Winter Coat', 'Tommy Hilfiger', 'Heavy wool blend double-breasted coat for men.', 9999, 14999, 90, 4.6, 150, 'winter_coat.jpg', 0],
            ['Aviator Sunglasses', 'Ray-Ban', 'Classic UV-protection metal frame aviator sunglasses.', 6499, 8499, 180, 4.7, 730, 'aviators.jpg', 0],
            ['Leather Belt & Wallet Set', 'WildHorn', 'Genuine leather bi-fold wallet and matching dress belt set.', 1199, 2499, 500, 4.1, 3420, 'belt_wallet.jpg', 0]
        ],
        // 4. Electronics
        4 => [
            ['Noise Cancelling Headphones', 'Sony', 'Industry leading active noise cancellation, smart ambient sound.', 24990, 29990, 120, 4.8, 1890, 'noise_headphones.jpg', 1],
            ['Portable Bluetooth Speaker', 'JBL', 'IP67 waterproof portable bluetooth speaker with deep bass.', 9999, 12999, 250, 4.6, 2950, 'bt_speaker.jpg', 1],
            ['Smart Fitness Band', 'Fitbit', 'Advanced health and fitness tracker with built-in GPS.', 8999, 11999, 150, 4.3, 840, 'fitness_band.jpg', 0],
            ['4K Action Camera', 'GoPro', 'HyperSmooth 5.0 video stabilization, waterproof, dual screens.', 32990, 39990, 70, 4.7, 630, 'action_camera.jpg', 0],
            ['Wireless Charging Pad', 'Anker', 'High-speed wireless Qi-charging pad with safety controls.', 1499, 2499, 400, 4.4, 3820, 'charging_pad.jpg', 0],
            ['Smart Security Camera', 'TP-Link', '1080p full HD home security Wi-Fi camera with night vision.', 1999, 3499, 500, 4.2, 5120, 'security_camera.jpg', 0],
            ['10000mAh Power Bank', 'Mi', 'Dual USB ports, 18W fast charge, metallic slim casing.', 1299, 1999, 800, 4.3, 12450, 'power_bank.jpg', 0],
            ['Ergonomic Wireless Mouse', 'Logitech', 'Unmatched precision, wireless connection, customized buttons.', 5999, 7999, 300, 4.6, 2130, 'wireless_mouse.jpg', 0],
            ['USB-C Hub Adapter', 'Satechi', 'Multi-port adapter with HDMI, USB-C pass-through, SD reader.', 4999, 6999, 200, 4.4, 950, 'usbc_hub.jpg', 0],
            ['Professional Ring Light', 'Digitek', '18-inch LED ring light with tripod stand for video creation.', 2499, 4999, 350, 4.3, 1180, 'ring_light.jpg', 0]
        ],
        // 5. Home & Furniture (Home Decor)
        5 => [
            ['Decorative Ceramic Vase', 'Deco', 'Elegant hand-crafted ceramic vase for modern home decor.', 699, 1299, 300, 4.2, 940, 'ceramic_vase.jpg', 1],
            ['Soft Cotton Bedsheet Set', 'Spaces', 'Super soft 100% cotton double bedsheet with 2 pillow covers.', 1499, 2499, 500, 4.3, 2180, 'cotton_bedsheet.jpg', 0],
            ['Blackout Window Curtains', "D'Decor", 'Set of 2 room-darkening thermal insulated window curtains.', 1799, 2999, 400, 4.4, 1850, 'window_curtains.jpg', 1],
            ['LED Desk Lamp with USB', 'Philips', 'Dimmable LED desk lamp with touch controls and phone charger.', 1299, 2499, 280, 4.5, 1120, 'desk_lamp.jpg', 0],
            ['Non-Slip Bath Mat', 'Solimo', 'Quick-dry microfiber non-slip bath mat for bathroom floors.', 299, 599, 900, 4.1, 4120, 'bath_mat.jpg', 0],
            ['Scented Candle Set', 'Yankee', 'Aromatherapy soy wax scented candles with soothing fragrances.', 599, 999, 450, 4.3, 850, 'scented_candles.jpg', 0],
            ['Wall Clocks for Living Room', 'Ajanta', 'Classic design silent sweeps decorative wall clock.', 799, 1499, 350, 4.2, 2350, 'wall_clock.jpg', 0],
            ['Stainless Steel Water Bottle', 'Milton', 'Vacuum insulated hot and cold water bottle 1L.', 899, 1299, 600, 4.5, 3810, 'steel_bottle.jpg', 0],
            ['Kitchen Knife Block Set', 'Pigeon', '6-piece stainless steel kitchen knife set with wooden stand.', 499, 999, 480, 4.1, 1490, 'kitchen_knives.jpg', 0],
            ['Multi-Purpose Storage Drawer', 'Kuber', '4-tier plastic modular drawer organizer for closets.', 1199, 1999, 250, 4.2, 870, 'storage_drawer.jpg', 0]
        ],
        // 6. Beauty
        6 => [
            ['Hydrating Face Moisturizer', 'Neutrogena', 'Hydro Boost water gel moisturizer with hyaluronic acid.', 849, 1149, 350, 4.6, 3210, 'face_moisturizer.jpg', 1],
            ['Matte Liquid Lipstick', 'Maybelline', 'SuperStay matte ink liquid lipstick, longwear formula.', 549, 799, 500, 4.4, 8430, 'matte_lipstick.jpg', 1],
            ['Charcoal Peel-Off Face Mask', 'The Derma Co', 'Activated charcoal peel-off mask for blackhead removal.', 299, 499, 600, 4.1, 2190, 'charcoal_mask.jpg', 0],
            ['Vitamin C Face Serum', 'Mamaearth', 'Skin illuminating face serum with Vitamin C and Turmeric.', 599, 799, 450, 4.3, 5130, 'vitamin_c_serum.jpg', 0],
            ['Organic Argan Hair Oil', 'Wow Skin Science', 'Pure cold-pressed Moroccan argan oil for hair and skin care.', 399, 699, 550, 4.2, 4210, 'argan_hair_oil.jpg', 0],
            ['Eau De Parfum for Men', 'Villain', 'Strong and long-lasting luxury fragrance perfume.', 799, 1299, 300, 4.4, 1840, 'mens_perfume.jpg', 0],
            ['SPF 50 Sunscreen Gel', 'La Shield', 'Matte finish water-resistant oil-free sunscreen gel.', 649, 999, 420, 4.5, 2940, 'sunscreen_gel.jpg', 0],
            ['Cleansing Face Wash', 'Cetaphil', 'Gentle skin cleanser for dry to normal sensitive skin.', 349, 499, 800, 4.6, 6210, 'cleansing_facewash.jpg', 0],
            ['Makeup Brush Set', 'Vega', '10-piece professional cosmetic makeup brush collection.', 499, 899, 250, 4.2, 920, 'makeup_brushes.jpg', 0],
            ['Aloe Vera Soothing Gel', 'Forest Essentials', 'Pure aloe vera gel with soothing and healing properties.', 1199, 1499, 180, 4.5, 430, 'aloe_vera_gel.jpg', 0]
        ],
        // 7. Books
        7 => [
            ['Atomic Habits', 'James Clear', 'An easy and proven way to build good habits and break bad ones.', 449, 799, 500, 4.8, 12450, 'atomic_habits.jpg', 1],
            ['The Psychology of Money', 'Morgan Housel', 'Timeless lessons on wealth, greed, and happiness.', 299, 399, 650, 4.7, 9840, 'psychology_money.jpg', 1],
            ['Ikigai: The Japanese Secret', 'Hector Garcia', 'Find your ikigai and bring purpose and joy to each day.', 349, 599, 800, 4.6, 8120, 'ikigai_book.jpg', 0],
            ['Rich Dad Poor Dad', 'Robert Kiyosaki', 'What the rich teach their kids about money that the poor and middle class do not.', 249, 499, 1000, 4.5, 14120, 'rich_dad_poor_dad.jpg', 0],
            ['Sapiens: A Brief History', 'Yuval Noah Harari', 'Explores how biological evolution shaped our societies.', 399, 599, 450, 4.6, 6230, 'sapiens_book.jpg', 0],
            ['It Ends With Us', 'Colleen Hoover', 'A heart-wrenching novel about relationships and self-discovery.', 299, 499, 350, 4.4, 4850, 'ends_with_us.jpg', 0],
            ['The Alchemist', 'Paulo Coelho', 'A magical fable about following your dreams and listening to your heart.', 199, 349, 900, 4.6, 11240, 'alchemist_book.jpg', 0],
            ['Thinking, Fast and Slow', 'Daniel Kahneman', 'System 1 and System 2 cognitive processing details.', 449, 699, 280, 4.5, 3420, 'thinking_fast_slow.jpg', 0],
            ['Good Vibes, Good Life', 'Vex King', 'Self-love is the key to unlocking your greatness.', 299, 499, 520, 4.5, 2910, 'good_vibes_book.jpg', 0],
            ['Silent Patient', 'Alex Michaelides', 'A shocking psychological thriller about a woman\'s violence.', 249, 399, 400, 4.4, 3850, 'silent_patient.jpg', 0]
        ],
        // 8. Sports
        8 => [
            ['Leather Cricket Ball', 'SG', 'Hand-stitched premium alum tanned leather cricket ball.', 499, 899, 600, 4.3, 1420, 'cricket_ball.jpg', 1],
            ['Badminton Racket Set', 'YONEX', 'Set of 2 carbon fiber shafts rackets with cover bag.', 1499, 2499, 350, 4.5, 2980, 'badminton_racket.jpg', 1],
            ['PVC Yoga Mat', 'Aerolite', '6mm thick durable non-slip home workout yoga mat.', 399, 999, 800, 4.2, 5320, 'yoga_mat.jpg', 0],
            ['Gym Dumbbell Set 10kg', 'Rubx', '10kg hex rubber coated dumbbells set (5kg x 2).', 1899, 2999, 150, 4.5, 940, 'gym_dumbbells.jpg', 0],
            ['Football Size 5', 'Nivia', '32-panel hand stitched international standard football.', 699, 1199, 450, 4.4, 3810, 'football_ball.jpg', 0],
            ['Swimming Goggles', 'Speedo', 'Anti-fog UV protection swimming goggles for adults.', 799, 1299, 200, 4.3, 1120, 'swimming_goggles.jpg', 0],
            ['Hydration Backpack', 'CamelBak', 'Outdoor hydration backpack with 2L water bladder.', 2499, 3999, 90, 4.6, 240, 'hydration_backpack.jpg', 0],
            ['Professional Skipping Rope', 'Boldfit', 'Tangle-free skipping rope with heavy foam handles.', 199, 499, 1200, 4.1, 4120, 'skipping_rope.jpg', 0],
            ['Resistance Bands Set', 'Decathlon', '5 latex resistance loop bands with carrying bag.', 499, 999, 650, 4.2, 1850, 'resistance_bands.jpg', 0],
            ['Sports Water Bottle 1L', 'Nalgene', 'Wide-mouth BPA-free leakproof sports bottle.', 899, 1199, 400, 4.6, 920, 'sports_bottle.jpg', 0]
        ],
        // 9. Furniture
        9 => [
            ['Solid Wood Coffee Table', 'Urban Ladder', 'Sheesham wood contemporary coffee table for living room.', 4999, 9999, 80, 4.4, 450, 'coffee_table.jpg', 1],
            ['Ergonomic Office Chair', 'Featherlite', 'High-back mesh chair with lumbar support and adjustable arms.', 6999, 12999, 120, 4.5, 930, 'office_chair.jpg', 1],
            ['3-Seater Fabric Sofa', 'Wakefit', 'Super comfortable dense foam sofa with grey fabric upholstery.', 14999, 24999, 40, 4.6, 310, 'fabric_sofa.jpg', 0],
            ['Queen Engineered Wood Bed', 'Sleepyhead', 'Queen size bed with spacious under-mattress storage.', 11999, 19999, 50, 4.3, 180, 'wood_bed.jpg', 0],
            ['Wooden Study Desk', 'Green Soul', 'Spacious study/office desk with drawer compartments.', 3499, 6999, 150, 4.4, 520, 'study_desk.jpg', 0],
            ['Modern TV Cabinet Unit', 'Bluewud', 'Wall-mounted entertainment center console for large TVs.', 2499, 4999, 200, 4.1, 1420, 'tv_cabinet.jpg', 0],
            ['4-Tier Wooden Bookshelf', 'DeckUp', 'Tall open-shelf bookcase for bedroom or office decor.', 2199, 3999, 110, 4.2, 680, 'wood_bookshelf.jpg', 0],
            ['6-Drawer Chest of Drawers', 'Spacewood', 'Engineered wood dresser storage unit for clothes/accessories.', 5999, 9999, 60, 4.3, 240, 'chest_drawers.jpg', 0],
            ['Metal Shoe Rack Organizer', 'Home Centre', '3-tier compact metal storage frame rack for shoes.', 899, 1999, 350, 4.0, 1120, 'shoe_rack.jpg', 0],
            ['Luxury Bean Bag Chair', 'Sattva', 'Filled faux-leather premium XXXL comfort bean bag.', 1499, 2999, 250, 4.4, 1830, 'bean_bag.jpg', 0]
        ]
    ];

    echo "Inserting products into database...\n";
    
    $insertStmt = $pdo->prepare("
        INSERT INTO products (
            category_id, name, slug, description, price, original_price, discount, brand, stock_quantity, rating, reviews, image, status, featured
        ) VALUES (
            :category_id, :name, :slug, :description, :price, :original_price, :discount, :brand, :stock_quantity, :rating, :reviews, :image, 1, :featured
        )
    ");

    $totalInserted = 0;
    foreach ($categoriesProducts as $catId => $productsList) {
        echo "Processing Category ID $catId...\n";
        foreach ($productsList as $p) {
            $name = $p[0];
            $brand = $p[1];
            $description = $p[2];
            $price = $p[3];
            $original_price = $p[4];
            $stock_quantity = $p[5];
            $rating = $p[6];
            $reviews = $p[7];
            $image = $p[8];
            $featured = $p[9];
            
            // Compute slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            
            // Compute discount percentage
            $discount = 0;
            if ($original_price > $price) {
                $discount = (int)round((($original_price - $price) / $original_price) * 100);
            }
            
            $insertStmt->execute([
                ':category_id' => $catId,
                ':name' => $name,
                ':slug' => $slug,
                ':description' => $description,
                ':price' => $price,
                ':original_price' => $original_price,
                ':discount' => $discount,
                ':brand' => $brand,
                ':stock_quantity' => $stock_quantity,
                ':rating' => $rating,
                ':reviews' => $reviews,
                ':image' => $image,
                ':featured' => $featured
            ]);
            
            $totalInserted++;
        }
    }
    
    echo "\nSuccess! Inserted $totalInserted products across 9 categories.\n";
    
} catch (Throwable $e) {
    echo "\nError during seeding: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
