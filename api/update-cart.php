<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'غير مسجل']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$cart_id = isset($data['cart_id']) ? (int)$data['cart_id'] : 0;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;
$user_id = $_SESSION['user_id'];

if ($cart_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير صحيحة']);
    exit;
}

// التحقق من المخزون
$check_query = "SELECT c.*, p.stock FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.id = $cart_id AND c.user_id = $user_id";
$result = $conn->query($check_query);

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'العنصر غير موجود']);
    exit;
}

$item = $result->fetch_assoc();

if ($quantity > $item['stock']) {
    echo json_encode(['success' => false, 'message' => 'الكمية المطلوبة أكبر من المتوفر']);
    exit;
}

// تحديث الكمية
$update_query = "UPDATE cart SET quantity = $quantity WHERE id = $cart_id";
if ($conn->query($update_query)) {
    echo json_encode(['success' => true, 'message' => 'تم التحديث']);
} else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ']);
}
?>