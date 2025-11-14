<?php
require_once 'config.php';

$search_query = isset($_GET['q']) ? cleanInput($_GET['q']) : '';

if (empty($search_query)) {
    redirect('products.php');
}

// البحث في المنتجات
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE p.name LIKE '%$search_query%' 
                   OR p.description LIKE '%$search_query%'
                   OR c.name LIKE '%$search_query%'
                   ORDER BY p.created_at DESC";
$products_result = $conn->query($products_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتائج البحث عن: <?php echo $search_query; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="products-page">
        <div class="container">
            <div class="page-header">
                <h1>نتائج البحث عن: "<?php echo $search_query; ?>"</h1>
                <p>تم العثور على <?php echo $products_result->num_rows; ?> منتج</p>
            </div>
            
            <?php if ($products_result->num_rows > 0): ?>
                <div class="products-grid">
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
                                        <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary">أضف للسلة</button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" disabled>نفذ من المخزون</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-products" style="text-align: center; padding: 60px 0;">
                    <p style="font-size: 48px; margin-bottom: 20px;">🔍</p>
                    <h2>لم يتم العثور على نتائج</h2>
                    <p>جرب البحث بكلمات أخرى أو <a href="products.php">تصفح جميع المنتجات</a></p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>