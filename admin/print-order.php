<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$order_query = "SELECT o.*, u.name as customer_name, u.email, u.phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = $order_id";
$order_result = $conn->query($order_query);

if ($order_result->num_rows == 0) {
    die('الطلب غير موجود');
}

$order = $order_result->fetch_assoc();

$items_query = "SELECT oi.*, p.name 
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
    <title>فاتورة - طلب #<?php echo $order_id; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: white;
        }
        .invoice {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 2px solid #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .info-box {
            padding: 15px;
            background: #f5f5f5;
            border-right: 4px solid #333;
        }
        .info-box h3 {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .info-box p {
            margin-bottom: 5px;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        table th,
        table td {
            padding: 12px;
            text-align: right;
            border: 1px solid #ddd;
        }
        table th {
            background: #333;
            color: white;
        }
        .total-section {
            text-align: left;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
            font-size: 18px;
        }
        .total-row.grand-total {
            background: #333;
            color: white;
            font-weight: bold;
            font-size: 22px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 16px; cursor: pointer;">🖨️ طباعة</button>
        <button onclick="window.close()" style="padding: 10px 30px; font-size: 16px; cursor: pointer; margin-right: 10px;">✖ إغلاق</button>
    </div>
    
    <div class="invoice">
        <div class="header">
            <h1><?php echo SITE_NAME; ?></h1>
            <p>فاتورة مبيعات</p>
            <h2>رقم الطلب: #<?php echo $order_id; ?></h2>
            <p>التاريخ: <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
        </div>
        
        <div class="info-section">
            <div class="info-box">
                <h3>معلومات العميل</h3>
                <p><strong>الاسم:</strong> <?php echo $order['customer_name']; ?></p>
                <p><strong>البريد:</strong> <?php echo $order['email']; ?></p>
                <p><strong>الجوال:</strong> <?php echo $order['phone'] ?: 'غير متوفر'; ?></p>
            </div>
            
            <div class="info-box">
                <h3>عنوان الشحن</h3>
                <p><?php echo nl2br($order['shipping_address']); ?></p>
            </div>
        </div>
        
        <div class="info-box" style="margin-bottom: 20px;">
            <h3>حالة الطلب</h3>
            <p><strong>الحالة:</strong> 
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
            </p>
            <p><strong>طريقة الدفع:</strong> 
                <?php 
                $payment_methods = [
                    'cod' => 'الدفع عند الاستلام',
                    'bank' => 'تحويل بنكي',
                    'credit' => 'بطاقة ائتمانية'
                ];
                echo $payment_methods[$order['payment_method']];
                ?>
            </p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المنتج</th>
                    <th>السعر</th>
                    <th>الكمية</th>
                    <th>المجموع</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                while ($item = $items_result->fetch_assoc()): 
                ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo $item['name']; ?></td>
                        <td><?php echo $item['price']; ?> <?php echo CURRENCY; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo $item['price'] * $item['quantity']; ?> <?php echo CURRENCY; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="total-section">
            <div class="total-row grand-total">
                <span>المجموع الإجمالي:</span>
                <span><?php echo $order['total_amount']; ?> <?php echo CURRENCY; ?></span>
            </div>
        </div>
        
        <?php if ($order['notes']): ?>
            <div class="info-box" style="margin-top: 30px;">
                <h3>ملاحظات</h3>
                <p><?php echo nl2br($order['notes']); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            <p><strong>شكراً لتسوقكم معنا!</strong></p>
            <p>للاستفسارات: 0500000000 | info@store.com</p>
        </div>
    </div>
    
    <script>
        // طباعة تلقائية عند فتح الصفحة
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>