<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// إحصائيات لوحة التحكم
$stats = [];

// عدد الطلبات
$orders_count = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$stats['orders'] = $orders_count;

// عدد المنتجات
$products_count = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$stats['products'] = $products_count;

// عدد العملاء
$customers_count = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'")->fetch_assoc()['total'];
$stats['customers'] = $customers_count;

// إجمالي المبيعات
$total_sales = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'")->fetch_assoc()['total'] ?? 0;
$stats['sales'] = $total_sales;

// أحدث الطلبات
$recent_orders_query = "SELECT o.*, u.name as customer_name 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 5";
$recent_orders = $conn->query($recent_orders_query);

// منتجات قليلة المخزون
$low_stock_query = "SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5";
$low_stock = $conn->query($low_stock_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>لوحة التحكم</h1>
                <div class="admin-actions">
                    <a href="../index.php" class="btn btn-outline">عرض المتجر</a>
                    <a href="../logout.php" class="btn btn-secondary">تسجيل الخروج</a>
                </div>
            </div>
            
            <?php displayMessage(); ?>
            
            <!-- بطاقات الإحصائيات -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['orders']; ?></h3>
                        <p>إجمالي الطلبات</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🛍️</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['products']; ?></h3>
                        <p>المنتجات</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['customers']; ?></h3>
                        <p>العملاء</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['sales'], 2); ?> <?php echo CURRENCY; ?></h3>
                        <p>إجمالي المبيعات</p>
                    </div>
                </div>
            </div>
            
            <!-- أحدث الطلبات -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>أحدث الطلبات</h2>
                    <a href="orders.php" class="btn btn-primary">عرض الكل</a>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo $order['customer_name']; ?></td>
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
                </div>
            </div>
            
            <!-- منتجات قليلة المخزون -->
            <div class="dashboard-section" >
                <div class="section-header">
                    <h2>تنبيهات المخزون</h2>
                    <a href="products.php" class="btn btn-primary">إدارة المنتجات</a>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>السعر</th>
                                <th>المخزون</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $low_stock->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $product['price']; ?> <?php echo CURRENCY; ?></td>
                                    <td>
                                        <span class="stock-badge <?php echo $product['stock'] == 0 ? 'out-of-stock' : 'low-stock'; ?>">
                                            <?php echo $product['stock']; ?> قطعة
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn-icon">✏️</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>