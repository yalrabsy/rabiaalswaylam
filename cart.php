<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// حذف منتج من السلة
if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $conn->query("DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
    showMessage('تم حذف المنتج من السلة');
    redirect('cart.php');
}

// تحديث الكمية عبر AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_id'])) {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        $check_query = "SELECT p.stock FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.id = $cart_id AND c.user_id = $user_id";
        $check_result = $conn->query($check_query);
        
        if ($check_result->num_rows > 0) {
            $stock = $check_result->fetch_assoc()['stock'];
            if ($quantity <= $stock) {
                $conn->query("UPDATE cart SET quantity = $quantity WHERE id = $cart_id AND user_id = $user_id");
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'الكمية أكبر من المتوفر']);
            }
        }
    }
    exit;
}

// جلب منتجات السلة
$cart_query = "SELECT c.*, p.name, p.price, p.discount_price, p.image, p.stock 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = $user_id
               ORDER BY c.created_at DESC";
$cart_result = $conn->query($cart_query);

$subtotal = 0;
$items = [];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سلة التسوق - <?php echo SITE_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f3ff 0%, #eff6ff 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .page-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .cart-icon {
            color: #7c3aed;
            font-size: 2.5rem;
        }
        
        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }
        
        .cart-section {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }
        
        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: #f9fafb;
            border-radius: 1rem;
            transition: all 0.3s;
        }
        
        .cart-item:hover {
            background: #f3f4f6;
            transform: translateY(-2px);
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 1rem;
            overflow: hidden;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: bold;
            font-size: 1.125rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .item-price {
            color: #7c3aed;
            font-weight: 600;
            font-size: 1.125rem;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            border-radius: 0.75rem;
            padding: 0.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .qty-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .qty-btn:hover {
            background: #f3f4f6;
        }
        
        .qty-display {
            width: 3rem;
            text-align: center;
            font-weight: bold;
            font-size: 1.125rem;
        }
        
        .item-total {
            font-weight: bold;
            font-size: 1.25rem;
            color: #1f2937;
            min-width: 100px;
            text-align: left;
        }
        
        .btn-remove {
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            color: #ef4444;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1.25rem;
            transition: all 0.3s;
        }
        
        .btn-remove:hover {
            background: #fee2e2;
        }
        
        .order-summary {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: sticky;
            top: 2rem;
        }
        
        .summary-rows {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            color: #6b7280;
        }
        
        .summary-row span:last-child {
            font-weight: 600;
        }
        
        .summary-divider {
            border-top: 2px solid #e5e7eb;
            padding-top: 1rem;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
        }
        
        .summary-total span:last-child {
            color: #7c3aed;
        }
        
        .btn-checkout {
            width: 100%;
            padding: 1.25rem;
            border: none;
            border-radius: 1rem;
            background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%);
            color: white;
            font-size: 1.125rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.4);
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(124, 58, 237, 0.5);
        }
        
        .btn-checkout:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .benefits {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .benefit-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .empty-cart {
            text-align: center;
            padding: 5rem 2rem;
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .empty-cart-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-cart h2 {
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .empty-cart p {
            color: #6b7280;
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%);
            color: white;
            text-decoration: none;
            border-radius: 1rem;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(124, 58, 237, 0.4);
        }
        
        .btn-secondary {
            display: inline-block;
            padding: 1rem 2rem;
            background: transparent;
            color: #7c3aed;
            text-decoration: none;
            border: 2px solid #7c3aed;
            border-radius: 1rem;
            font-weight: bold;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        
        .btn-secondary:hover {
            background: #7c3aed;
            color: white;
        }
        
        @media (max-width: 1024px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 640px) {
            .cart-item {
                flex-wrap: wrap;
            }
            
            .item-total {
                width: 100%;
                text-align: right;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>
                <span class="cart-icon">🛒</span>
                سلة التسوق
            </h1>
        </div>
        
        <?php if ($cart_result->num_rows > 0): ?>
            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-section">
                    <h2 class="section-title">سلة التسوق (<?php echo $cart_result->num_rows; ?> منتجات)</h2>
                    
                    <div class="cart-items">
                        <?php while ($item = $cart_result->fetch_assoc()): 
                            $price = $item['discount_price'] ?: $item['price'];
                            $item_total = $price * $item['quantity'];
                            $subtotal += $item_total;
                            $items[] = $item;
                        ?>
                            <div class="cart-item" id="cart-item-<?php echo $item['id']; ?>">
                                <div class="item-image">
                                    <?php if ($item['image']): ?>
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                    <?php else: ?>
                                        <span>📦</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-details">
                                    <div class="item-name"><?php echo $item['name']; ?></div>
                                    <div class="item-price"><?php echo $price; ?> ريال</div>
                                </div>
                                
                                <div class="quantity-control">
                                    <button class="qty-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">−</button>
                                    <span class="qty-display" id="qty-<?php echo $item['id']; ?>"><?php echo $item['quantity']; ?></span>
                                    <button class="qty-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                </div>
                                
                                <div class="item-total" id="total-<?php echo $item['id']; ?>">
                                    <?php echo $item_total; ?> ريال
                                </div>
                                
                                <button class="btn-remove" onclick="removeItem(<?php echo $item['id']; ?>)">🗑️</button>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <a href="products.php" class="btn-secondary" style="display: inline-block; margin-top: 1.5rem;">← متابعة التسوق</a>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <h2 class="section-title">ملخص الطلب</h2>
                    
                    <div class="summary-rows">
                        <div class="summary-row">
                            <span>المجموع الفرعي</span>
                            <span id="subtotal"><?php echo number_format($subtotal, 2); ?> ريال</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>الشحن</span>
                            <span id="shipping">
                                <?php 
                                $shipping = $subtotal >= 200 ? 0 : 50;
                                echo $shipping == 0 ? 'مجاناً' : number_format($shipping, 2) . ' ريال'; 
                                ?>
                            </span>
                        </div>
                        
                        <div class="summary-row">
                            <span>الضريبة (15%)</span>
                            <span id="tax"><?php echo number_format($subtotal * 0.15, 2); ?> ريال</span>
                        </div>
                        
                        <div class="summary-row summary-divider">
                            <div class="summary-total">
                                <span>الإجمالي</span>
                                <span id="grand-total"><?php echo number_format($subtotal + $shipping + ($subtotal * 0.15), 2); ?> ريال</span>
                            </div>
                        </div>
                    </div>
                    
                    <a href="checkout.php" class="btn-checkout">تأكيد الطلب</a>
                    
                    <div class="benefits">
                        <div class="benefit-item">
                            <span>✅</span>
                            <span>شحن مجاني للطلبات فوق 200 ريال</span>
                        </div>
                        <div class="benefit-item">
                            <span>🔒</span>
                            <span>الدفع آمن ومشفر 100%</span>
                        </div>
                        <div class="benefit-item">
                            <span>📦</span>
                            <span>التوصيل خلال 2-3 أيام عمل</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h2>السلة فارغة</h2>
                <p>لم تقم بإضافة أي منتجات بعد</p>
                <a href="products.php" class="btn-primary">تصفح المنتجات</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        const cartItems = <?php echo json_encode($items); ?>;
        
        async function updateQuantity(cartId, change) {
            const qtyDisplay = document.getElementById('qty-' + cartId);
            let currentQty = parseInt(qtyDisplay.textContent);
            let newQty = currentQty + change;
            
            if (newQty < 1) return;
            
            try {
                const formData = new FormData();
                formData.append('cart_id', cartId);
                formData.append('quantity', newQty);
                
                const response = await fetch('cart.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'حدث خطأ');
                }
            } catch (error) {
                alert('حدث خطأ في التحديث');
            }
        }
        
        function removeItem(cartId) {
            if (confirm('هل تريد حذف هذا المنتج من السلة؟')) {
                window.location.href = 'cart.php?remove=' + cartId;
            }
        }
    </script>
</body>
</html>