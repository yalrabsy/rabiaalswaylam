<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// جلب طلبات المستخدم
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = $conn->query($orders_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="orders-page">
        <div class="container">
            <h1>طلباتي</h1>
            
            <?php if ($orders_result->num_rows > 0): ?>
                <div class="orders-list">
                    <?php while ($order = $orders_result->fetch_assoc()): 
                        $order_id = $order['id'];
                        $items_query = "SELECT oi.*, p.name, p.image 
                                       FROM order_items oi 
                                       JOIN products p ON oi.product_id = p.id 
                                       WHERE oi.order_id = $order_id";
                        $items_result = $conn->query($items_query);
                    ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <h3>طلب #<?php echo $order['id']; ?></h3>
                                    <p>تاريخ الطلب: <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php 
                                        $statuses = [
                                            'pending' => 'قيد الانتظار',
                                            'processing' => 'قيد المعالجة',
                                            'shipped' => 'تم الشحن',
                                            'delivered' => 'تم التوصيل',
                                            'cancelled' => 'ملغي'
                                        ];
                                        echo $statuses[$order['status']];
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="order-items">
                                <?php while ($item = $items_result->fetch_assoc()): ?>
                                    <div class="order-item">
                                        <img src="<?php echo $item['image'] ?: 'assets/images/no-image.jpg'; ?>" 
                                             alt="<?php echo $item['name']; ?>">
                                        <div class="item-info">
                                            <h4><?php echo $item['name']; ?></h4>
                                            <p>الكمية: <?php echo $item['quantity']; ?></p>
                                            <p class="price"><?php echo $item['price']; ?> <?php echo CURRENCY; ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="order-footer">
                                <div class="order-total">
                                    <strong>المجموع:</strong>
                                    <span><?php echo $order['total_amount']; ?> <?php echo CURRENCY; ?></span>
                                </div>
                                <div class="order-actions">
                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">
                                        عرض التفاصيل
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-orders">
                    <p>📦</p>
                    <h2>لا توجد طلبات</h2>
                    <p>لم تقم بطلب أي منتجات بعد</p>
                    <a href="products.php" class="btn btn-primary">تصفح المنتجات</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>