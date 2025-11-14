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
                    <h1><?php echo SITE_NAME; ?></h1>
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
                    <form action="search.php" method="GET">
                        <input type="text" name="q" placeholder="ابحث عن منتج...">
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
                            <span>مرحباً، <?php echo $_SESSION['user_name']; ?></span>
                            <div class="dropdown">
                                <button class="dropbtn">القائمة</button>
                                <div class="dropdown-content">
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