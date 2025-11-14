<?php
require_once '../config.php';

header('Content-Type: application/json');

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'not_logged_in']);
    exit;
}

// الحصول على البيانات
$data = json_decode(file_get_contents('php://input'), true);
$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف المنتج غير صالح']);
    exit;
}

// التحقق من وجود المنتج
$product_query = "SELECT * FROM products WHERE id = $product_id";
$product_result = $conn->query($product_query);

if ($product_result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
    exit;
}

$product = $product_result->fetch_assoc();

// التحقق من المخزون
if ($product['stock'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'المنتج غير متوفر في المخزون']);
    exit;
}

// التحقق من وجود المنتج في السلة
$check_query = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
$check_result = $conn->query($check_query);

if ($check_result->num_rows > 0) {
    // تحديث الكمية
    $cart_item = $check_result->fetch_assoc();
    $new_quantity = $cart_item['quantity'] + 1;
    
    if ($new_quantity > $product['stock']) {
        echo json_encode(['success' => false, 'message' => 'الكمية المطلوبة أكبر من المتوفر']);
        exit;
    }
    
    $update_query = "UPDATE cart SET quantity = $new_quantity WHERE id = {$cart_item['id']}";
    $conn->query($update_query);
} else {
    // إضافة منتج جديد
    $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)";
    $conn->query($insert_query);
}

echo json_encode(['success' => true, 'message' => 'تم إضافة المنتج إلى السلة']);
?>