<?php
// جلب عدد المنتجات في السلة
$cart_count = 0;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $cart_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id";
    $cart_result = $conn->query($cart_query);
    $cart_data = $cart_result->fetch_assoc();
    $cart_count = $cart_data['total'] ?? 0;
}
?>
<header class="main-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="index.php">
                    <?php if (defined('SITE_LOGO') && SITE_LOGO): ?>
                        <img src="<?php echo SITE_LOGO; ?>" alt="<?php echo SITE_NAME; ?>" style="max-height: 50px;">
                    <?php else: ?>
                        <h1><?php echo SITE_NAME; ?></h1>
                    <?php endif; ?>
                </a>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">الرئيسية</a></li>
                    <li><a href="products.php">المنتجات</a></li>
                    <li><a href="about.php">من نحن</a></li>
                    <li><a href="contact.php">اتصل بنا</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="search-box">
                    <form action="products.php" method="GET">
                        <input type="text" name="search" placeholder="ابحث عن منتج...">
                        <button type="submit">🔍</button>
                    </form>
                </div>
                
                <div class="user-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="cart.php" class="cart-icon">
                            🛒
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="user-menu">
                            <button class="user-btn">مرحباً، <?php echo $_SESSION['user_name']; ?> ▼</button>
                            <div class="dropdown-menu">
                                <a href="profile.php">حسابي</a>
                                <a href="orders.php">طلباتي</a>
                                <?php if (isAdmin()): ?>
                                    <a href="admin/index.php">لوحة التحكم</a>
                                <?php endif; ?>
                                <a href="logout.php">تسجيل الخروج</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">تسجيل الدخول</a>
                        <a href="register.php" class="btn btn-primary">إنشاء حساب</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php displayMessage(); ?>
</header>

<style>
.user-menu {
    position: relative;
}

.user-btn {
    background: none;
    border: none;
    color: var(--dark-color);
    font-size: 16px;
    cursor: pointer;
    padding: 8px 15px;
    font-weight: 500;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
    min-width: 200px;
    z-index: 1000;
    margin-top: 10px;
}

.user-menu:hover .dropdown-menu {
    display: block;
}

.dropdown-menu a {
    display: block;
    padding: 12px 20px;
    color: var(--dark-color);
    text-decoration: none;
    transition: background 0.3s;
}

.dropdown-menu a:hover {
    background: var(--light-color);
}
</style>