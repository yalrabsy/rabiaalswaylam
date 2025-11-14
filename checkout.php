<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user = $conn->query($user_query)->fetch_assoc();

$cart_query = "SELECT c.*, p.name, p.price, p.discount_price, p.stock, p.image 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = $user_id";
$cart_result = $conn->query($cart_query);

if ($cart_result->num_rows == 0) {
    showMessage('السلة فارغة', 'error');
    redirect('cart.php');
}

$subtotal = 0;
$items = [];
while ($item = $cart_result->fetch_assoc()) {
    $price = $item['discount_price'] ?: $item['price'];
    $subtotal += $price * $item['quantity'];
    $items[] = $item;
}

$shipping = $subtotal >= 500 ? 0 : 30;
$tax = $subtotal * 0.15;
$total = $subtotal + $shipping + $tax;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $phone = cleanInput($_POST['phone']);
    $email = cleanInput($_POST['email']);
    $city = cleanInput($_POST['city']);
    $address = cleanInput($_POST['address']);
    $payment_method = cleanInput($_POST['payment_method']);
    
    if (empty($name)) $errors[] = 'الاسم مطلوب';
    if (empty($phone)) $errors[] = 'رقم الجوال مطلوب';
    if (empty($city)) $errors[] = 'المدينة مطلوبة';
    if (empty($address)) $errors[] = 'العنوان مطلوب';
    
    if (empty($errors)) {
        $shipping_address = "$name\n$phone\n$address\n$city";
        $conn->begin_transaction();
        
        try {
            $insert_order = "INSERT INTO orders (user_id, total_amount, payment_method, shipping_address, status) 
                           VALUES ($user_id, $total, '$payment_method', '$shipping_address', 'pending')";
            
            if ($conn->query($insert_order)) {
                $order_id = $conn->insert_id;
                
                foreach ($items as $item) {
                    $product_id = $item['product_id'];
                    $quantity = $item['quantity'];
                    $price = $item['discount_price'] ?: $item['price'];
                    
                    $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                VALUES ($order_id, $product_id, $quantity, $price)");
                    $conn->query("UPDATE products SET stock = stock - $quantity WHERE id = $product_id");
                }
                
                $conn->query("DELETE FROM cart WHERE user_id = $user_id");
                $conn->commit();
                
                redirect('order-success.php?order=' . $order_id);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'حدث خطأ: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إتمام الطلب - <?php echo SITE_NAME; ?></title>
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
        
        .page-header h1 {
            font-size: 2.5rem;
            color: #1f2937;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .checkout-left {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .white-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #7c3aed;
        }
        
        textarea.form-input {
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }
        
        .payment-options {
            display: grid;
            gap: 1rem;
        }
        
        .payment-card {
            border: 2px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .payment-card:hover {
            border-color: #7c3aed;
            background: #faf5ff;
        }
        
        .payment-card input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .payment-details {
            flex: 1;
        }
        
        .payment-title {
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .payment-desc {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .payment-emoji {
            font-size: 2rem;
        }
        
        .card-info-fields {
            display: none;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #e5e7eb;
            animation: slideDown 0.3s ease;
        }
        
        .card-info-fields.active {
            display: block;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .order-summary {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: sticky;
            top: 2rem;
        }
        
        .summary-items {
            max-height: 350px;
            overflow-y: auto;
            margin-bottom: 1.5rem;
        }
        
        .summary-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 1rem;
            margin-bottom: 0.75rem;
        }
        
        .item-img {
            width: 70px;
            height: 70px;
            border-radius: 0.75rem;
            overflow: hidden;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
        }
        
        .item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            font-size: 0.9375rem;
            margin-bottom: 0.25rem;
        }
        
        .item-qty {
            font-size: 0.8125rem;
            color: #6b7280;
        }
        
        .item-price {
            font-weight: bold;
            color: #7c3aed;
            margin-top: 0.25rem;
        }
        
        .summary-totals {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
            padding: 1.25rem 0;
            border-top: 2px solid #e5e7eb;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            color: #6b7280;
        }
        
        .total-row .value {
            font-weight: 600;
        }
        
        .grand-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.5rem;
            font-weight: bold;
            padding-top: 1rem;
            border-top: 2px solid #e5e7eb;
            color: #1f2937;
        }
        
        .grand-total .value {
            color: #7c3aed;
        }
        
        .submit-btn {
            width: 100%;
            padding: 1.25rem;
            background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 1rem;
            font-size: 1.125rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
            margin-top: 1.5rem;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.5);
        }
        
        .benefits-list {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }
        
        .benefit {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .error-alert {
            background: #fee2e2;
            border: 2px solid #ef4444;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .error-alert strong {
            display: block;
            margin-bottom: 0.75rem;
            color: #dc2626;
        }
        
        .error-alert ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }
        
        .error-alert li {
            color: #dc2626;
        }
        
        @media (max-width: 1024px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
        }
        
        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>
                <span>🛒</span>
                إتمام الطلب
            </h1>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-alert">
                <strong>⚠️ يرجى تصحيح الأخطاء التالية:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li>• <?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="checkout-form">
            <div class="checkout-grid">
                <!-- Left Column -->
                <div class="checkout-left">
                    <!-- Shipping Info -->
                    <div class="white-card">
                        <h2 class="card-title">📍 معلومات الشحن</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" name="name" class="form-input" 
                                       placeholder="الاسم الكامل" 
                                       value="<?php echo $user['name']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <input type="email" name="email" class="form-input" 
                                       placeholder="البريد الإلكتروني" 
                                       value="<?php echo $user['email']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <input type="tel" name="phone" class="form-input" 
                                       placeholder="رقم الجوال" 
                                       value="<?php echo $user['phone']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <input type="text" name="city" class="form-input" 
                                       placeholder="المدينة" 
                                       value="<?php echo $user['city']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <textarea name="address" class="form-input" 
                                      placeholder="العنوان الكامل" required><?php echo $user['address']; ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="white-card">
                        <h2 class="card-title">💳 معلومات الدفع</h2>
                        
                        <div class="payment-options">
                            <label class="payment-card">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <div class="payment-details">
                                    <div class="payment-title">💵 الدفع عند الاستلام</div>
                                    <div class="payment-desc">ادفع نقداً عند استلام طلبك</div>
                                </div>
                                <span class="payment-emoji">🚚</span>
                            </label>
                            
                            <label class="payment-card">
                                <input type="radio" name="payment_method" value="bank">
                                <div class="payment-details">
                                    <div class="payment-title">🏦 تحويل بنكي</div>
                                    <div class="payment-desc">قم بالتحويل وأرسل إيصال الدفع</div>
                                </div>
                                <span class="payment-emoji">💳</span>
                            </label>
                            
                            <label class="payment-card" onclick="toggleCardFields()">
                                <input type="radio" name="payment_method" value="credit" id="credit-card-radio">
                                <div class="payment-details">
                                    <div class="payment-title">💳 بطاقة ائتمانية</div>
                                    <div class="payment-desc">ادفع ببطاقتك بشكل آمن</div>
                                </div>
                                <span class="payment-emoji">🔒</span>
                            </label>
                            
                            <div id="card-info" class="card-info-fields">
                                <div class="form-group full-width">
                                    <input type="text" class="form-input" 
                                           placeholder="رقم البطاقة" 
                                           maxlength="19" required>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <input type="text" class="form-input" 
                                               placeholder="تاريخ الانتهاء (MM/YY)" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-input" 
                                               placeholder="CVV" maxlength="3" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Order Summary -->
                <div class="order-summary">
                    <h2 class="card-title">ملخص الطلب</h2>
                    
                    <div class="summary-items">
                        <?php foreach ($items as $item): 
                            $price = $item['discount_price'] ?: $item['price'];
                        ?>
                            <div class="summary-item">
                                <div class="item-img">
                                    <?php if ($item['image']): ?>
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                    <?php else: ?>
                                        <span>📦</span>
                                    <?php endif; ?>
                                </div>
                                <div class="item-info">
                                    <div class="item-name"><?php echo $item['name']; ?></div>
                                    <div class="item-qty">الكمية: <?php echo $item['quantity']; ?></div>
                                    <div class="item-price"><?php echo $price * $item['quantity']; ?> ريال</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="total-row">
                            <span>المجموع الفرعي</span>
                            <span class="value"><?php echo number_format($subtotal, 2); ?> ريال</span>
                        </div>
                        
                        <div class="total-row">
                            <span>الشحن</span>
                            <span class="value"><?php echo $shipping == 0 ? 'مجاناً' : number_format($shipping, 2) . ' ريال'; ?></span>
                        </div>
                        
                        <div class="total-row">
                            <span>الضريبة (15%)</span>
                            <span class="value"><?php echo number_format($tax, 2); ?> ريال</span>
                        </div>
                        
                        <div class="grand-total">
                            <span>الإجمالي</span>
                            <span class="value"><?php echo number_format($total, 2); ?> ريال</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">تأكيد الطلب</button>
                    
                    <div class="benefits-list">
                        <div class="benefit">
                            <span>✅</span>
                            <span>شحن مجاني للطلبات فوق 500 ريال</span>
                        </div>
                        <div class="benefit">
                            <span>🔒</span>
                            <span>الدفع آمن ومشفر 100%</span>
                        </div>
                        <div class="benefit">
                            <span>📦</span>
                            <span>التوصيل خلال 2-3 أيام عمل</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        function toggleCardFields() {
            const cardInfo = document.getElementById('card-info');
            const creditRadio = document.getElementById('credit-card-radio');
            
            if (creditRadio.checked) {
                cardInfo.classList.add('active');
            } else {
                cardInfo.classList.remove('active');
            }
        }
        
        // إظهار حقول البطاقة عند اختيار الدفع بالبطاقة
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const cardInfo = document.getElementById('card-info');
                if (this.value === 'credit') {
                    cardInfo.classList.add('active');
                } else {
                    cardInfo.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>