<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// إضافة فئة جديدة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = cleanInput($_POST['name']);
    $description = cleanInput($_POST['description']);
    
    if (!empty($name)) {
        $conn->query("INSERT INTO categories (name, description) VALUES ('$name', '$description')");
        showMessage('تم إضافة الفئة بنجاح');
        redirect('categories.php');
    }
}

// حذف فئة
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM categories WHERE id = $id");
    showMessage('تم حذف الفئة بنجاح');
    redirect('categories.php');
}

// جلب الفئات
$categories_query = "SELECT c.*, COUNT(p.id) as products_count 
                     FROM categories c 
                     LEFT JOIN products p ON c.id = p.id 
                     GROUP BY c.id 
                     ORDER BY c.name";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الفئات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>إدارة الفئات</h1>
            </div>
            
            <?php displayMessage(); ?>
            
            <div class="admin-card">
                <h3>إضافة فئة جديدة</h3>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>اسم الفئة *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>الوصف</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary">إضافة الفئة</button>
                </form>
            </div>
            
            <div class="admin-card">
                <h3>الفئات الحالية</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>الرقم</th>
                                <th>اسم الفئة</th>
                                <th>الوصف</th>
                                <th>عدد المنتجات</th>
                                <th>تاريخ الإنشاء</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($category = $categories_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><strong><?php echo $category['name']; ?></strong></td>
                                    <td><?php echo $category['description']; ?></td>
                                    <td><?php echo $category['products_count']; ?> منتج</td>
                                    <td><?php echo date('Y-m-d', strtotime($category['created_at'])); ?></td>
                                    <td class="actions-cell">
                                        <a href="edit-category.php?id=<?php echo $category['id']; ?>" class="btn-icon" title="تعديل">✏️</a>
                                        <a href="categories.php?delete=<?php echo $category['id']; ?>" 
                                           class="btn-icon" 
                                           title="حذف"
                                           onclick="return confirm('هل أنت متأكد من حذف هذه الفئة؟')">🗑️</a>
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