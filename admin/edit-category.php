<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id == 0) {
    redirect('categories.php');
}

// جلب بيانات الفئة
$category_query = "SELECT * FROM categories WHERE id = $category_id";
$category_result = $conn->query($category_query);

if ($category_result->num_rows == 0) {
    showMessage('الفئة غير موجودة', 'error');
    redirect('categories.php');
}

$category = $category_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $description = cleanInput($_POST['description']);
    
    if (!empty($name)) {
        $conn->query("UPDATE categories SET name = '$name', description = '$description' WHERE id = $category_id");
        showMessage('تم تحديث الفئة بنجاح');
        redirect('categories.php');
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الفئة - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>تعديل الفئة: <?php echo $category['name']; ?></h1>
                <div class="admin-actions">
                    <a href="categories.php" class="btn btn-outline">← رجوع</a>
                </div>
            </div>
            
            <div class="admin-card">
                <form method="POST">
                    <div class="form-group">
                        <label>اسم الفئة *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $category['name']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>الوصف</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo $category['description']; ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                        <a href="categories.php" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>