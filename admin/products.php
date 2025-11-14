<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// حذف منتج
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $id");
    showMessage('تم حذف المنتج بنجاح');
    redirect('products.php');
}

// جلب المنتجات
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   ORDER BY p.created_at DESC";
$products_result = $conn->query($products_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>إدارة المنتجات</h1>
                <div class="admin-actions">
                    <a href="add-product.php" class="btn btn-primary">+ إضافة منتج جديد</a>
                </div>
            </div>
            
            <?php displayMessage(); ?>
            
            <div class="admin-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>الصورة</th>
                                <th>اسم المنتج</th>
                                <th>الفئة</th>
                                <th>السعر</th>
                                <th>المخزون</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $products_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo $product['image'] ?: '../assets/images/no-image.jpg'; ?>" 
                                             alt="<?php echo $product['name']; ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $product['category_name']; ?></td>
                                    <td>
                                        <?php if ($product['discount_price']): ?>
                                            <span style="text-decoration: line-through; color: #999;">
                                                <?php echo $product['price']; ?>
                                            </span>
                                            <strong><?php echo $product['discount_price']; ?></strong>
                                        <?php else: ?>
                                            <strong><?php echo $product['price']; ?></strong>
                                        <?php endif; ?>
                                        <?php echo CURRENCY; ?>
                                    </td>
                                    <td>
                                        <span class="stock-badge <?php echo $product['stock'] == 0 ? 'out-of-stock' : ($product['stock'] < 10 ? 'low-stock' : ''); ?>">
                                            <?php echo $product['stock']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($product['featured']): ?>
                                            <span class="badge badge-success">مميز</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">عادي</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="edit-product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn-icon" title="تعديل">✏️</a>
                                        <a href="products.php?delete=<?php echo $product['id']; ?>" 
                                           class="btn-icon" 
                                           title="حذف"
                                           onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">🗑️</a>
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