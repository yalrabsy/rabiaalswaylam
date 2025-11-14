<?php
require_once 'config.php';

// الفلترة والبحث
$where = "1=1";
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

if ($category_filter > 0) {
    $where .= " AND p.category_id = $category_filter";
}

if (!empty($search)) {
    $where .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

// جلب المنتجات
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE $where 
                   ORDER BY p.created_at DESC";
$products_result = $conn->query($products_query);

// جلب الفئات للفلتر
$categories_query = "SELECT * FROM categories";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المنتجات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="products-page">
        <div class="container">
            <div class="page-header">
                <h1>جميع المنتجات</h1>
            </div>
            
            <div class="products-layout">
                <!-- الشريط الجانبي للفلترة -->
                <aside class="filters-sidebar">
                    <div class="filter-section">
                        <h3>الفئات</h3>
                        <ul class="category-filter">
                            <li>
                                <a href="products.php" <?php echo $category_filter == 0 ? 'class="active"' : ''; ?>>
                                    جميع الفئات
                                </a>
                            </li>
                            <?php while ($category = $categories_result->fetch_assoc()): ?>
                                <li>
                                    <a href="products.php?category=<?php echo $category['id']; ?>" 
                                       <?php echo $category_filter == $category['id'] ? 'class="active"' : ''; ?>>
                                        <?php echo $category['name']; ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                    
                    <div class="filter-section">
                        <h3>السعر</h3>
                        <form method="GET" action="products.php">
                            <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
                            <div class="price-range">
                                <input type="number" name="min_price" placeholder="من" class="form-control">
                                <input type="number" name="max_price" placeholder="إلى" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">تطبيق</button>
                        </form>
                    </div>
                </aside>
                
                <!-- عرض المنتجات -->
                <div class="products-content">
                    <div class="products-header">
                        <p>عرض <?php echo $products_result->num_rows; ?> منتج</p>
                        <select class="sort-select" onchange="location = this.value;">
                            <option value="products.php">الأحدث</option>
                            <option value="products.php?sort=price_asc">السعر: من الأقل للأعلى</option>
                            <option value="products.php?sort=price_desc">السعر: من الأعلى للأقل</option>
                        </select>
                    </div>
                    
                    <div class="products-grid">
                        <?php if ($products_result->num_rows > 0): ?>
                            <?php while ($product = $products_result->fetch_assoc()): ?>
                                <div class="product-card">
                                    <div class="product-image">
                                        <img src="<?php echo $product['image'] ?: 'assets/images/no-image.jpg'; ?>" 
                                             alt="<?php echo $product['name']; ?>">
                                        <?php if ($product['discount_price']): ?>
                                            <span class="discount-badge">
                                                خصم <?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($product['stock'] == 0): ?>
                                            <span class="out-of-stock">نفذ من المخزون</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <h3><?php echo $product['name']; ?></h3>
                                        <p class="category"><?php echo $product['category_name']; ?></p>
                                        <div class="price">
                                            <?php if ($product['discount_price']): ?>
                                                <span class="original-price"><?php echo $product['price']; ?> <?php echo CURRENCY; ?></span>
                                                <span class="discount-price"><?php echo $product['discount_price']; ?> <?php echo CURRENCY; ?></span>
                                            <?php else: ?>
                                                <span class="current-price"><?php echo $product['price']; ?> <?php echo CURRENCY; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-actions">
                                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">عرض التفاصيل</a>
                                            <?php if ($product['stock'] > 0): ?>
                                                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary">
                                                    أضف للسلة
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-products">
                                <p>لا توجد منتجات متاحة حالياً</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>