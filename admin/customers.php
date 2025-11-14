<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// جلب العملاء
$customers_query = "SELECT u.*, 
                    COUNT(DISTINCT o.id) as orders_count,
                    COALESCE(SUM(o.total_amount), 0) as total_spent
                    FROM users u
                    LEFT JOIN orders o ON u.id = o.user_id
                    WHERE u.role = 'customer'
                    GROUP BY u.id
                    ORDER BY u.created_at DESC";
$customers_result = $conn->query($customers_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العملاء - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>إدارة العملاء</h1>
            </div>
            
            <div class="admin-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>الرقم</th>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الجوال</th>
                                <th>المدينة</th>
                                <th>عدد الطلبات</th>
                                <th>إجمالي المشتريات</th>
                                <th>تاريخ التسجيل</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($customer = $customers_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $customer['id']; ?></td>
                                    <td><strong><?php echo $customer['name']; ?></strong></td>
                                    <td><?php echo $customer['email']; ?></td>
                                    <td><?php echo $customer['phone'] ?: '-'; ?></td>
                                    <td><?php echo $customer['city'] ?: '-'; ?></td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?php echo $customer['orders_count']; ?> طلب
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="color: var(--primary-color);">
                                            <?php echo number_format($customer['total_spent'], 2); ?> <?php echo CURRENCY; ?>
                                        </strong>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($customer['created_at'])); ?></td>
                                    <td class="actions-cell">
                                        <a href="customer-details.php?id=<?php echo $customer['id']; ?>" 
                                           class="btn-icon" title="عرض التفاصيل">👁️</a>
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