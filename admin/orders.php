<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// تحديث حالة الطلب
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = cleanInput($_POST['status']);
    
    $conn->query("UPDATE orders SET status = '$status' WHERE id = $order_id");
    showMessage('تم تحديث حالة الطلب');
    redirect('orders.php');
}

// جلب الطلبات
$orders_query = "SELECT o.*, u.name as customer_name, u.email as customer_email 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 ORDER BY o.created_at DESC";
$orders_result = $conn->query($orders_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلبات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>إدارة الطلبات</h1>
            </div>
            
            <?php displayMessage(); ?>
            
            <div class="admin-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>البريد الإلكتروني</th>
                                <th>المبلغ</th>
                                <th>طريقة الدفع</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                    <td><?php echo $order['customer_name']; ?></td>
                                    <td><?php echo $order['customer_email']; ?></td>
                                    <td><strong><?php echo $order['total_amount']; ?> <?php echo CURRENCY; ?></strong></td>
                                    <td>
                                        <?php 
                                        $payment_methods = [
                                            'cod' => 'الدفع عند الاستلام',
                                            'bank' => 'تحويل بنكي',
                                            'credit' => 'بطاقة ائتمانية'
                                        ];
                                        echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                                        ?>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="form-control" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>قيد المعالجة</option>
                                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>تم الشحن</option>
                                                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>تم التوصيل</option>
                                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>ملغي</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                    <td class="actions-cell">
                                        <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="عرض التفاصيل">👁️</a>
                                        <a href="print-order.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="طباعة" target="_blank">🖨️</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>