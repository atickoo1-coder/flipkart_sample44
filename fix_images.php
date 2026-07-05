<?php
/**
 * Run this script once to assign correct product images in the database.
 * Open: http://localhost/flipkart_sample/fix_images.php
 */
require_once __DIR__ . '/includes/customer_auth.php';

$pdo = getConnection();

$imageMap = [
    '%iPhone 15 Pro Max%' => 'iphone15promax.png',
    '%Samsung Galaxy S24 Ultra%' => 'samsung_s24u.png',
    '%OnePlus 12%' => 'oneplus12.png',
    '%MacBook Air M3%' => 'macbook_air_m3.png',
    '%Dell XPS 15%' => 'dell_xps15.png',
    '%Cotton T-Shirt%' => 'cotton_tshirt.png',
    '%Sony WH-1000XM5%' => 'sony_wh1000xm5.png',
    '%JBL Flip 6%' => 'jbl_flip6.png'
];

$updated = 0;
foreach ($imageMap as $search => $imageFile) {
    $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE name LIKE ? AND (image IS NULL OR image = '')");
    $stmt->execute([$imageFile, $search]);
    $updated += $stmt->rowCount();
}

$stmt = $pdo->prepare("UPDATE products SET image = 'placeholder.png' WHERE image IS NULL OR image = ''");
$stmt->execute();
$fallback = $stmt->rowCount();

$baseUrl = getBaseUrl();
echo "<!DOCTYPE html><html><head><style>
body{font-family:sans-serif;max-width:600px;margin:40px auto;padding:20px;text-align:center}
.success{color:#388e3c;font-size:18px;margin:16px 0}
.btn{display:inline-block;padding:10px 24px;background:#2874f0;color:#fff;text-decoration:none;border-radius:4px;margin-top:16px}
</style></head><body>";
echo "<h2>Product Images Fixed</h2>";
echo "<div class='success'>Products updated with actual images: $updated</div>";
echo "<div>Products with placeholder fallback: $fallback</div>";
echo "<a class='btn' href='$baseUrl/index.php'>Go to Homepage</a>";
echo "</body></html>";
