<?php
require_once '../config.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? cleanInput($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

// البحث في المنتجات
$search_query = "SELECT id, name, price, discount_price, image 
                 FROM products 
                 WHERE name LIKE '%$query%' 
                 OR description LIKE '%$query%'
                 LIMIT 5";

$result = $conn->query($search_query);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'price' => $row['discount_price'] ?: $row['price'],
        'image' => $row['image'] ?: 'assets/images/no-image.jpg'
    ];
}

echo json_encode(['results' => $products]);
?>