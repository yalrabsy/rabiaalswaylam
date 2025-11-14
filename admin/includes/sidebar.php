<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2><?php echo SITE_NAME; ?></h2>
        <p>لوحة التحكم</p>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="index.php" class="nav-link">
                    <span class="icon">📊</span>
                    <span>الرئيسية</span>
                </a>
            </li>
            
            <li>
                <a href="products.php" class="nav-link">
                    <span class="icon">🛍️</span>
                    <span>المنتجات</span>
                </a>
            </li>
            
            <li>
                <a href="categories.php" class="nav-link">
                    <span class="icon">📁</span>
                    <span>الفئات</span>
                </a>
            </li>
            
            <li>
                <a href="orders.php" class="nav-link">
                    <span class="icon">📦</span>
                    <span>الطلبات</span>
                </a>
            </li>
            
            <li>
                <a href="customers.php" class="nav-link">
                    <span class="icon">👥</span>
                    <span>العملاء</span>
                </a>
            </li>
            
            <li>
                <a href="settings.php" class="nav-link">
                    <span class="icon">⚙️</span>
                    <span>الإعدادات</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<style>
    .admin-sidebar {
        width: 250px;
        background: var(--dark-color);
        color: #fff;
        height: 100vh;
        position: fixed;
        right: 0;
        top: 0;
        overflow-y: auto;
    }
    
    .sidebar-header {
        padding: 30px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        text-align: center;
    }
    
    .sidebar-header h2 {
        color: #fff;
        margin-bottom: 5px;
    }
    
    .sidebar-header p {
        color: rgba(255,255,255,0.6);
        font-size: 14px;
    }
    
    .sidebar-nav ul {
        list-style: none;
        padding: 20px 0;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 20px;
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .nav-link:hover,
    .nav-link.active {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border-right: 3px solid var(--primary-color);
    }
    
    .nav-link .icon {
        font-size: 20px;
    }
</style>