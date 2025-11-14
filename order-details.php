<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// جلب بيانات الطلب
$order_query = "SELECT o.*, u.name as customer_name, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = $order_id AND o.user_id = $user_id";
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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .order-details-page {
            padding: 60px 0;
            background: #f8fafc;
        }
        .order-header-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-top: 20px;
        }
        .info-box h4 {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .info-box p {
            font-size: 18px;
            font-weight: 600;
        }
        .items-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .order-item-row {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .order-item-row:last-child {
            border-bottom: none;
        }
        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            margin-left: 20px;
        }
        .item-details {
            flex: 1;
        }
        .item-price {
            font-weight: bold;
            color: var(--primary-color);
        }
        .order-summary {
            background: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        .summary-total {
            font-size: 24px;
            font-weight: bold;
            border-top: 2px solid var(--border-color);
            padding-top: 15px;
            margin-top: 15px;
        }
        @media (max-width: 768px) {
            .order-info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="order-details-page">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1>تفاصيل الطلب #<?php echo $order_id; ?></h1>
                <a href="orders.php" class="btn btn-outline">← العودة للطلبات</a>
            </div>
            
            <div class="order-header-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2>الطلب #<?php echo $order['id']; ?></h2>
                        <p style="color: #64748b;">تاريخ الطلب: <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
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
                
                <div class="order-info-grid">
                    <div class="info-box">
                        <h4>العميل</h4>
                        <p><?php echo $order['customer_name']; ?></p>
                    </div>
                    
                    <div class="info-box">
                        <h4>البريد الإلكتروني</h4>
                        <p><?php echo $order['email']; ?></p>
                    </div>
                    
                    <div class="info-box">
                        <h4>طريقة الدفع</h4>
                        <p>
                            <?php 
                            $payment_methods = [
                                'cod' => 'الدفع عند الاستلام',
                                'bank' => 'تحويل بنكي',
                                'credit' => 'بطاقة ائتمانية'
                            ];
                            echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                            ?>
                        </p>
                    </div>
                    
                    <div class="info-box">
                        <h4>المبلغ الإجمالي</h4>
                        <p style="color: var(--primary-color);"><?php echo $order['total_amount']; ?> <?php echo CURRENCY; ?></p>
                    </div>
                </div>
                
                <?php if ($order['shipping_address']): ?>
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                        <h4 style="color: #64748b; margin-bottom: 10px;">عنوان الشحن</h4>
                        <p><?php echo nl2br($order['shipping_address']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($order['notes']): ?>
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                        <h4 style="color: #64748b; margin-bottom: 10px;">ملاحظات</h4>
                        <p><?php echo nl2br($order['notes']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="items-card">
                <h3 style="margin-bottom: 20px;">المنتجات المطلوبة</h3>
                
                <?php while ($item = $items_result->fetch_assoc()): ?>
                    <div class="order-item-row">
                        <img src="<?php echo $item['image'] ?: 'assets/images/no-image.jpg'; ?>" 
                             alt="<?php echo $item['name']; ?>" 
                             class="item-image">
                        <div class="item-details">
                            <h4><?php echo $item['name']; ?></h4>
                            <p style="color: #64748b;">الكمية: <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="item-price">
                            <?php echo $item['price']; ?> <?php echo CURRENCY; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <div class="order-summary">
                    <div class="summary-row">
                        <span>المجموع الفرعي:</span>
                        <span><?php echo $order['total_amount']; ?> <?php echo CURRENCY; ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>المجموع الكلي:</span>
                        <span><?php echo $order['total_amount']; ?> <?php echo CURRENCY; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>