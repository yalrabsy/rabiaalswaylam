<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$order_id = isset($_GET['order']) ? (int)$_GET['order'] : 0;
$user_id = $_SESSION['user_id'];

if ($order_id == 0) {
    redirect('orders.php');
}

// جلب بيانات الطلب
$order_query = "SELECT o.*, u.name, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = $order_id AND o.user_id = $user_id";
$order_result = $conn->query($order_query);

if ($order_result->num_rows == 0) {
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
    <title>تم إتمام طلبك بنجاح - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .success-page {
            padding: 80px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .success-card {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 50px;
            animation: scaleIn 0.5s ease;
        }
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        .success-card h1 {
            font-size: 36px;
            color: #10b981;
            margin-bottom: 15px;
        }
        .order-number {
            font-size: 24px;
            color: var(--primary-color);
            font-weight: bold;
            margin: 20px 0;
            padding: 15px;
            background: #f0f9ff;
            border-radius: 8px;
        }
        .order-details {
            text-align: right;
            margin: 30px 0;
            padding: 30px;
            background: #f8fafc;
            border-radius: 12px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        .status-timeline {
            margin: 30px 0;
            padding: 30px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
        }
        .timeline-step {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .timeline-step:last-child {
            margin-bottom: 0;
        }
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .step-icon.active {
            background: var(--primary-color);
            color: white;
        }
        .step-info h4 {
            margin-bottom: 5px;
        }
        .step-info p {
            font-size: 14px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="success-page">
        <div class="success-card">
            <div class="success-icon">✓</div>
            <h1>تم إتمام طلبك بنجاح!</h1>
            <p style="font-size: 18px; color: #64748b; margin-bottom: 20px;">
                شكراً لك على ثقتك بنا. سنبدأ في معالجة طلبك فوراً
            </p>
            
            <div class="order-number">
                رقم الطلب: #<?php echo $order_id; ?>
            </div>
            
            <div class="order-details">
                <h3 style="margin-bottom: 20px;">تفاصيل الطلب</h3>
                
                <div class="detail-row">
                    <span><strong>التاريخ:</strong></span>
                    <span><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></span>
                </div>
                
                <div class="detail-row">
                    <span><strong>المبلغ الإجمالي:</strong></span>
                    <span style="color: var(--primary-color); font-weight: bold; font-size: 20px;">
                        <?php echo $order['total_amount']; ?> <?php echo CURRENCY; ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span><strong>طريقة الدفع:</strong></span>
                    <span>
                        <?php 
                        $payment_methods = [
                            'cod' => '💵 الدفع عند الاستلام',
                            'bank' => '🏦 تحويل بنكي',
                            'credit' => '💳 بطاقة ائتمانية'
                        ];
                        echo $payment_methods[$order['payment_method']];
                        ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span><strong>حالة الطلب:</strong></span>
                    <span>
                        <span style="background: #fef3c7; color: #92400e; padding: 5px 15px; border-radius: 20px; font-size: 14px;">
                            قيد الانتظار
                        </span>
                    </span>
                </div>
            </div>
            
            <div class="status-timeline">
                <h3 style="margin-bottom: 20px; text-align: right;">مراحل الطلب</h3>
                
                <div class="timeline-step">
                    <div class="step-icon active">✓</div>
                    <div class="step-info">
                        <h4>تم استلام الطلب</h4>
                        <p>تم استلام طلبك وجاري المراجعة</p>
                    </div>
                </div>
                
                <div class="timeline-step">
                    <div class="step-icon">📦</div>
                    <div class="step-info">
                        <h4>جاري التجهيز</h4>
                        <p>سيتم تجهيز طلبك وتغليفه</p>
                    </div>
                </div>
                
                <div class="timeline-step">
                    <div class="step-icon">🚚</div>
                    <div class="step-info">
                        <h4>جاري الشحن</h4>
                        <p>سيتم شحن طلبك إلى عنوانك</p>
                    </div>
                </div>
                
                <div class="timeline-step">
                    <div class="step-icon">✅</div>
                    <div class="step-info">
                        <h4>تم التوصيل</h4>
                        <p>سيصلك طلبك في الموعد المحدد</p>
                    </div>
                </div>
            </div>
            
            <div style="background: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: right;">
                <h4 style="margin-bottom: 10px;">📧 تم إرسال تأكيد الطلب</h4>
                <p style="color: #64748b;">
                    تم إرسال تفاصيل طلبك إلى بريدك الإلكتروني: <strong><?php echo $order['email']; ?></strong>
                </p>
            </div>
            
            <div class="action-buttons">
                <a href="order-details.php?id=<?php echo $order_id; ?>" class="btn btn-primary">
                    عرض تفاصيل الطلب
                </a>
                <a href="orders.php" class="btn btn-outline">
                    طلباتي
                </a>
                <a href="products.php" class="btn btn-secondary">
                    متابعة التسوق
                </a>
            </div>
            
            <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid var(--border-color);">
                <p style="color: #64748b;">
                    لديك استفسار؟ <a href="contact.php" style="color: var(--primary-color);">تواصل معنا</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>