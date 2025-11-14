<?php
require_once 'config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id == 0) {
    redirect('products.php');
}

// جلب بيانات المنتج
$product_query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = $product_id";
$result = $conn->query($product_query);

if ($result->num_rows == 0) {
    showMessage('المنتج غير موجود', 'error');
    redirect('products.php');
}

$product = $result->fetch_assoc();

// جلب منتجات مشابهة
$related_query = "SELECT * FROM products 
                  WHERE category_id = {$product['category_id']} 
                  AND id != $product_id 
                  LIMIT 4";
$related_result = $conn->query($related_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .product-detail {
            padding: 60px 0;
        }
        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-bottom: 60px;
        }
        .product-gallery img {
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .product-details h1 {
            font-size: 32px;
            margin-bottom: 15px;
        }
        .product-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            color: var(--secondary-color);
        }
        .product-price {
            margin: 30px 0;
        }
        .price-current {
            font-size: 36px;
            font-weight: bold;
            color: var(--primary-color);
        }
        .price-old {
            font-size: 24px;
            text-decoration: line-through;
            color: var(--secondary-color);
            margin-right: 15px;
        }
        .stock-info {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            margin: 15px 0;
        }
        .in-stock {
            background: #d1fae5;
            color: #065f46;
        }
        .out-of-stock {
            background: #fee2e2;
            color: #991b1b;
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 30px 0;
        }
        .quantity-input {
            width: 80px;
            padding: 10px;
            text-align: center;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 18px;
        }
        .add-to-cart-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            font-size: 18px;
        }
        .product-description {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid var(--border-color);
        }
        .related-products {
            margin-top: 60px;
        }
        @media (max-width: 768px) {
            .product-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="product-detail">
        <div class="container">
            <div class="product-layout">
                <!-- معرض الصور -->
                <div class="product-gallery">
                    <img src="<?php echo $product['image'] ?: 'assets/images/no-image.jpg'; ?>" 
                         alt="<?php echo $product['name']; ?>">
                </div>
                
                <!-- تفاصيل المنتج -->
                <div class="product-details">
                    <h1><?php echo $product['name']; ?></h1>
                    
                    <div class="product-meta">
                        <span>الفئة: <?php echo $product['category_name']; ?></span>
                        <span>رقم المنتج: #<?php echo $product['id']; ?></span>
                    </div>
                    
                    <div class="product-price">
                        <?php if ($product['discount_price']): ?>
                            <span class="price-old"><?php echo $product['price']; ?> <?php echo CURRENCY; ?></span>
                            <span class="price-current"><?php echo $product['discount_price']; ?> <?php echo CURRENCY; ?></span>
                            <span class="discount-percent">
                                خصم <?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%
                            </span>
                        <?php else: ?>
                            <span class="price-current"><?php echo $product['price']; ?> <?php echo CURRENCY; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <span class="stock-info in-stock">✓ متوفر في المخزون (<?php echo $product['stock']; ?> قطعة)</span>
                    <?php else: ?>
                        <span class="stock-info out-of-stock">✗ غير متوفر حالياً</span>
                    <?php endif; ?>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <div class="quantity-selector">
                            <label>الكمية:</label>
                            <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                        </div>
                        
                        <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary add-to-cart-btn">
                            🛒 أضف إلى السلة
                        </button>
                    <?php endif; ?>
                    
                    <div class="product-description">
                        <h3>وصف المنتج</h3>
                        <p><?php echo nl2br($product['description']); ?></p>
                    </div>
                    
                    <div class="product-features">
                        <h3>مميزات المنتج</h3>
                        <ul>
                            <li>✓ شحن مجاني للطلبات فوق 200 ريال</li>
                            <li>✓ إمكانية الإرجاع خلال 14 يوم</li>
                            <li>✓ ضمان الجودة</li>
                            <li>✓ دفع آمن ومضمون</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- منتجات مشابهة -->
            <?php if ($related_result->num_rows > 0): ?>
                <div class="related-products">
                    <h2>منتجات مشابهة</h2>
                    <div class="products-grid">
                        <?php while ($related = $related_result->fetch_assoc()): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo $related['image'] ?: 'assets/images/no-image.jpg'; ?>" 
                                         alt="<?php echo $related['name']; ?>">
                                </div>
                                <div class="product-info">
                                    <h3><?php echo $related['name']; ?></h3>
                                    <div class="price">
                                        <?php if ($related['discount_price']): ?>
                                            <span class="original-price"><?php echo $related['price']; ?> <?php echo CURRENCY; ?></span>
                                            <span class="discount-price"><?php echo $related['discount_price']; ?> <?php echo CURRENCY; ?></span>
                                        <?php else: ?>
                                            <span class="current-price"><?php echo $related['price']; ?> <?php echo CURRENCY; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-actions">
                                        <a href="product.php?id=<?php echo $related['id']; ?>" class="btn btn-secondary">عرض</a>
                                        <button onclick="addToCart(<?php echo $related['id']; ?>)" class="btn btn-primary">أضف</button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>