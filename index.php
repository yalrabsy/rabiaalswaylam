<?php
require_once 'config.php';

// جلب إعدادات البانر
$banner_query = "SELECT banner_image, banner_title, banner_subtitle FROM settings WHERE id = 1";
$banner_result = $conn->query($banner_query);
$banner = $banner_result ? $banner_result->fetch_assoc() : null;

// جلب المنتجات المميزة
$featured_query = "SELECT p.*, c.name as category_name FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE p.featured = 1 LIMIT 6";
$featured_result = $conn->query($featured_query);

// جلب الفئات
$categories_query = "SELECT * FROM categories LIMIT 4";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - الصفحة الرئيسية</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero {
            position: relative;
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            overflow: hidden;
        }
        
        <?php if ($banner && $banner['banner_image']): ?>
        .hero {
            background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?php echo $banner['banner_image']; ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        <?php endif; ?>
        
        .hero .container {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: fadeInUp 0.8s ease;
        }
        
        .hero p {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <!-- قسم البانر الرئيسي -->
        <section class="hero">
            <div class="container">
                <h1><?php echo $banner && $banner['banner_title'] ? $banner['banner_title'] : 'مرحباً بك في ' . SITE_NAME; ?></h1>
                <p><?php echo $banner && $banner['banner_subtitle'] ? $banner['banner_subtitle'] : 'أفضل المنتجات بأفضل الأسعار'; ?></p>
                <a href="products.php" class="btn btn-primary">تسوق الآن</a>
            </div>
        </section>

        <!-- قسم الفئات -->
        <section class="categories">
            <div class="container">
                <h2>تصفح حسب الفئة</h2>
                <div class="categories-grid">
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                        <div class="category-card">
                            <a href="products.php?category=<?php echo $category['id']; ?>">
                                <h3><?php echo $category['name']; ?></h3>
                                <p><?php echo $category['description']; ?></p>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>

        <!-- قسم المنتجات المميزة -->
        <section class="featured-products">
            <div class="container">
                <h2>منتجات مميزة</h2>
                <div class="products-grid">
                    <?php while ($product = $featured_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo $product['image'] ?: 'assets/images/no-image.jpg'; ?>" 
                                     alt="<?php echo $product['name']; ?>">
                                <?php if ($product['discount_price']): ?>
                                    <span class="discount-badge">خصم</span>
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
                                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary">أضف للسلة</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="text-center">
                    <a href="products.php" class="btn btn-outline">عرض جميع المنتجات</a>
                </div>
            </div>
        </section>

        <!-- قسم المميزات -->
        <section class="features">
            <div class="container">
                <div class="features-grid">
                    <div class="feature">
                        <i>🚚</i>
                        <h3>شحن مجاني</h3>
                        <p>للطلبات فوق 200 ريال</p>
                    </div>
                    <div class="feature">
                        <i>💳</i>
                        <h3>دفع آمن</h3>
                        <p>حماية كاملة للمدفوعات</p>
                    </div>
                    <div class="feature">
                        <i>↩️</i>
                        <h3>إرجاع مجاني</h3>
                        <p>خلال 14 يوم من الشراء</p>
                    </div>
                    <div class="feature">
                        <i>📞</i>
                        <h3>دعم على مدار الساعة</h3>
                        <p>خدمة العملاء متاحة دائماً</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>