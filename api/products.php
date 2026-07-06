<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();

    $search = trim($_GET['search'] ?? '');
    $categorySlug = trim($_GET['category'] ?? '');
    $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
    $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
    $sort = $_GET['sort'] ?? 'newest';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, min(60, (int)($_GET['per_page'] ?? 12)));
    $offset = ($page - 1) * $perPage;

    $where = ['p.status = 1'];
    $params = [];

    if ($search !== '') {
        $where[] = '(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)';
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($categorySlug !== '') {
        $where[] = 'c.slug = ?';
        $params[] = $categorySlug;
    }

    if ($minPrice > 0) {
        $where[] = 'p.price >= ?';
        $params[] = $minPrice;
    }

    if ($maxPrice > 0) {
        $where[] = 'p.price <= ?';
        $params[] = $maxPrice;
    }

    $whereClause = implode(' AND ', $where);

    $orderBy = match ($sort) {
        'price_low' => 'p.price ASC',
        'price_high' => 'p.price DESC',
        'name_asc' => 'p.name ASC',
        'name_desc' => 'p.name DESC',
        'rating' => 'p.rating DESC',
        default => 'p.created_at DESC'
    };

    $countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalProducts = (int)$countStmt->fetchColumn();

    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE $whereClause 
            ORDER BY $orderBy 
            LIMIT $perPage OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $baseUrl = getenv('APP_BASE_URL') ?: (getenv('RENDER_BACKEND_URL') ?: getenv('RENDER_EXTERNAL_URL') ?: 'https://flipkart-sample44-1.onrender.com');
    $baseUrl = rtrim($baseUrl, '/');

    foreach ($products as &$product) {
        $imageName = $product['image'] ?? 'placeholder.png';
        $product['image'] = ltrim($imageName, '/');
        $product['image_url'] = $baseUrl . '/uploads/' . ltrim($imageName, '/');
        $product['price'] = (float)$product['price'];
        $product['original_price'] = !empty($product['original_price']) ? (float)$product['original_price'] : null;
        $product['rating'] = (float)$product['rating'];
        $product['reviews'] = (int)$product['reviews'];
        $product['discount'] = (int)$product['discount'];
    }

    echo json_encode([
        'success' => true,
        'products' => $products,
        'total' => $totalProducts,
        'page' => $page,
        'per_page' => $perPage,
        'base_url' => $baseUrl,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
