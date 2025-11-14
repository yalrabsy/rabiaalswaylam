<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب بيانات العميل
$customer_query = "SELECT * FROM users WHERE id = $customer_id AND role = 'customer'";
$customer_result = $conn->query($customer_query);

if ($customer_result->num_rows == 0) {
    showMessage('العميل غير موجود', 'error');
    redirect('customers.php');
}

$customer = $customer_result->fetch_assoc();

// جلب طلبات العميل
$orders_query = "SELECT * FROM orders WHERE user_id = $customer_id ORDER BY created_at DESC";
$orders_result = $conn->query($orders_query);

// إحصائيات العميل
$stats_query = "SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_spent
                FROM orders 
                WHERE user_id = $customer_id AND status != 'cancelled'";
$stats = $conn->query($stats_query)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل العميل - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>تفاصيل العميل</h1>
                <div class="admin-actions">
                    <a href="customers.php" class="btn btn-outline">← رجوع</a>
                </div>
            </div>
            
            <div class="admin-card">
                <h3>المعلومات الشخصية</h3>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-top: 20px;">
                    <div>
                        <p style="color: #64748b; margin-bottom: 5px;">الاسم</p>
                        <h4><?php echo $customer['name']; ?></h4>
                    </div>
                    
                    <div>
                        <p style="color: #64748b; margin-bottom: 5px;">البريد الإلكتروني</p>
                        <h4><?php echo $customer['email']; ?></h4>
                    </div>
                    
                    <div>
                        <p style="color: #64748b; margin-bottom: 5px;">رقم الجوال</p>
                        <h4><?php echo $customer['phone'] ?: 'غير متوفر'; ?></h4>
                    </div>
                    
                    <div>
                        <p style="color: #64748b; margin-bottom: 5px;">المدينة</p>
                        <h4><?php echo $customer['city'] ?: 'غير متوفر'; ?></h4>
                    </div>
                    
                    <div>
                        <p style="color: #64748b; margin-bottom: 5px;">تاريخ التسجيل</p>
                        <h4><?php echo date('Y-m-d', strtotime($customer['created_at'])); ?></h4>
                    </div>
                </div>
                
                <?php if ($customer['address']): ?>
                    <div style="margin-top: 30px;">
                        <p style="color: #64748b; margin-bottom: 10px;">العنوان</p>
                        <p><?php echo nl2br($customer['address']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="stats-grid" style="margin: 30px 0;">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>إجمالي الطلبات</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_spent'], 2); ?> <?php echo CURRENCY; ?></h3>
                        <p>إجمالي المشتريات</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <h3>سجل الطلبات</h3>
                
                <?php if ($orders_result->num_rows > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                    <td><?php echo $order['total_amount']; ?> <?php echo CURRENCY; ?></td>
                                    <td>
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
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn-icon">👁️</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; padding: 40px; color: #64748b;">لا توجد طلبات لهذا العميل</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>