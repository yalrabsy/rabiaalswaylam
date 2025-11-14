<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// جلب الفئات
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $description = cleanInput($_POST['description']);
    $price = (float)$_POST['price'];
    $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $category_id = (int)$_POST['category_id'];
    $stock = (int)$_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // التحقق
    if (empty($name)) $errors[] = 'اسم المنتج مطلوب';
    if ($price <= 0) $errors[] = 'السعر يجب أن يكون أكبر من صفر';
    if ($category_id == 0) $errors[] = 'يرجى اختيار الفئة';
    
    // رفع الصورة
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../uploads/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'uploads/products/' . $file_name;
        }
    }
    
    if (empty($errors)) {
        $discount_sql = $discount_price ? "'$discount_price'" : "NULL";
        $image_sql = $image_path ? "'$image_path'" : "NULL";
        
        $insert_query = "INSERT INTO products (name, description, price, discount_price, category_id, stock, image, featured) 
                        VALUES ('$name', '$description', $price, $discount_sql, $category_id, $stock, $image_sql, $featured)";
        
        if ($conn->query($insert_query)) {
            showMessage('تم إضافة المنتج بنجاح');
            redirect('products.php');
        } else {
            $errors[] = 'حدث خطأ أثناء إضافة المنتج';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتج - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>إضافة منتج جديد</h1>
                <div class="admin-actions">
                    <a href="products.php" class="btn btn-outline">← رجوع</a>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>اسم المنتج *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>الفئة *</label>
                            <select name="category_id" class="form-control" required>
                                <option value="0">اختر الفئة</option>
                                <?php while ($category = $categories_result->fetch_assoc()): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>السعر *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label>سعر الخصم</label>
                            <input type="number" name="discount_price" class="form-control" step="0.01" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label>الكمية في المخزون *</label>
                            <input type="number" name="stock" class="form-control" min="0" value="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label>صورة المنتج</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>وصف المنتج</label>
                        <textarea name="description" class="form-control" rows="5"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="featured">
                            <span>منتج مميز</span>
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">حفظ المنتج</button>
                        <a href="products.php" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>