<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب بيانات الطلب
$order_query = "SELECT o.*, u.name as customer_name, u.email, u.phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = $order_id";
$order_result = $conn->query($order_query);

if ($order_result->num_rows == 0) {
    showMessage('الطلب غير موجود', 'error');
    redirect('orders.php');
}

$order = $order_result->fetch_assoc();

// جلب تفاصيل الطلب
$items_query = "SELECT oi.*, p.name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = $order_id";
$items_result = $conn->query($items_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الطلب #<?php echo $order_id; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>تفاصيل الطلب #<?php echo $order_id; ?></h1>
                <div class="admin-actions">
                    <a href="orders.php" class="btn btn-outline">← رجوع</a>
                    <a href="print-order.php?id=<?php echo $order_id; ?>" class="btn btn-secondary" target="_blank">🖨️ طباعة</a>
                </div>
            </div>
            
            <div class="admin-card">
                <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
                    <div>
                        <h2>معلومات الطلب</h2>
                        <p style="color: #64748b;">تاريخ: <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                    </div>
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
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 30px;">
                    <div>
                        <h4 style="color: #64748b; margin-bottom: 15px;">معلومات العميل</h4>
                        <p><strong>الاسم:</strong> <?php echo $order['customer_name']; ?></p>
                        <p><strong>البريد:</strong> <?php echo $order['email']; ?></p>
                        <p><strong>الجوال:</strong> <?php echo $order['phone'] ?: 'غير متوفر'; ?></p>
                    </div>
                    
                    <div>
                        <h4 style="color: #64748b; margin-bottom: 15px;">معلومات الشحن</h4>
                        <p><?php echo nl2br($order['shipping_address']); ?></p>
                    </div>
                    
                    <div>
                        <h4 style="color: #64748b; margin-bottom: 15px;">معلومات الدفع</h4>
                        <p><strong>الطريقة:</strong> 
                            <?php 
                            $payment_methods = [
                                'cod' => 'الدفع عند الاستلام',
                                'bank' => 'تحويل بنكي',
                                'credit' => 'بطاقة ائتمانية'
                            ];
                            echo $payment_methods[$order['payment_method']];
                            ?>
                        </p>
                        <p><strong>المبلغ:</strong> <?php echo $order['total_amount']; ?> <?php echo CURRENCY; ?></p>
                    </div>
                </div>
                
                <?php if ($order['notes']): ?>
                    <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                        <h4 style="margin-bottom: 10px;">ملاحظات العميل</h4>
                        <p><?php echo nl2br($order['notes']); ?></p>
                    </div>
                <?php endif; ?>
                
                <h3 style="margin-bottom: 20px;">المنتجات</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>المنتج</th>
                            <th>السعر</th>
                            <th>الكمية</th>
                            <th>المجموع</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="../<?php echo $item['image'] ?: 'assets/images/no-image.jpg'; ?>" 
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                </td>
                                <td><strong><?php echo $item['name']; ?></strong></td>
                                <td><?php echo $item['price']; ?> <?php echo CURRENCY; ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><strong><?php echo $item['price'] * $item['quantity']; ?> <?php echo CURRENCY; ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: var(--light-color);">
                            <td colspan="4" style="text-align: left;"><strong>المجموع الكلي:</strong></td>
                            <td><strong style="font-size: 18px; color: var(--primary-color);"><?php echo $order['total_amount']; ?> <?php echo CURRENCY; ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </main>
    </div>
</body>
</html>