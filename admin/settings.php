<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// جلب إعدادات المتجر من جدول الإعدادات
$settings_query = "SELECT * FROM settings WHERE id = 1";
$settings_result = $conn->query($settings_query);

if ($settings_result->num_rows == 0) {
    // إنشاء إعدادات افتراضية
    $conn->query("INSERT INTO settings (id, site_name, site_url, site_email, site_phone, site_address, currency, logo, favicon) 
                  VALUES (1, 'متجري الإلكتروني', 'http://localhost/store', 'info@store.com', '0500000000', 'الرياض، المملكة العربية السعودية', 'ر.س', '', '')");
    $settings_result = $conn->query($settings_query);
}

$settings = $settings_result->fetch_assoc();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // تحديث إعدادات المتجر
    if (isset($_POST['update_store'])) {
        $site_name = cleanInput($_POST['site_name']);
        $site_url = cleanInput($_POST['site_url']);
        $site_email = cleanInput($_POST['site_email']);
        $site_phone = cleanInput($_POST['site_phone']);
        $site_address = cleanInput($_POST['site_address']);
        $currency = cleanInput($_POST['currency']);
        $site_description = cleanInput($_POST['site_description']);
        $facebook = cleanInput($_POST['facebook']);
        $twitter = cleanInput($_POST['twitter']);
        $instagram = cleanInput($_POST['instagram']);
        $whatsapp = cleanInput($_POST['whatsapp']);
        
        $banner_title = cleanInput($_POST['banner_title']);
        $banner_subtitle = cleanInput($_POST['banner_subtitle']);
        
        // رفع اللوجو
        $logo = $settings['logo'];
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $upload_dir = '../uploads/settings/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $file_name = 'logo_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                if ($logo && file_exists('../' . $logo)) {
                    unlink('../' . $logo);
                }
                $logo = 'uploads/settings/' . $file_name;
            }
        }
        
        // رفع الفافيكون
        $favicon = $settings['favicon'];
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] == 0) {
            $upload_dir = '../uploads/settings/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
            $file_name = 'favicon_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['favicon']['tmp_name'], $target_file)) {
                if ($favicon && file_exists('../' . $favicon)) {
                    unlink('../' . $favicon);
                }
                $favicon = 'uploads/settings/' . $file_name;
            }
        }
        
        // رفع صورة البانر
        $banner_image = $settings['banner_image'];
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
            $upload_dir = '../uploads/settings/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'banner_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $target_file)) {
                if ($banner_image && file_exists('../' . $banner_image)) {
                    unlink('../' . $banner_image);
                }
                $banner_image = 'uploads/settings/' . $file_name;
            }
        }
        
        $update_query = "UPDATE settings SET 
                        site_name = '$site_name',
                        site_url = '$site_url',
                        site_email = '$site_email',
                        site_phone = '$site_phone',
                        site_address = '$site_address',
                        currency = '$currency',
                        site_description = '$site_description',
                        facebook = '$facebook',
                        twitter = '$twitter',
                        instagram = '$instagram',
                        whatsapp = '$whatsapp',
                        logo = '$logo',
                        favicon = '$favicon',
                        banner_image = '$banner_image',
                        banner_title = '$banner_title',
                        banner_subtitle = '$banner_subtitle'
                        WHERE id = 1";
        
        if ($conn->query($update_query)) {
            showMessage('تم تحديث إعدادات المتجر بنجاح');
            $settings = $conn->query($settings_query)->fetch_assoc();
        }
    }
    
    // تحديث معلومات المدير
    if (isset($_POST['update_admin'])) {
        $admin_name = cleanInput($_POST['admin_name']);
        $admin_email = cleanInput($_POST['admin_email']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $user_id = $_SESSION['user_id'];
        
        $password_update = '';
        if (!empty($new_password)) {
            $user_query = "SELECT password FROM users WHERE id = $user_id";
            $user = $conn->query($user_query)->fetch_assoc();
            
            if (md5($current_password) !== $user['password']) {
                $errors[] = 'كلمة المرور الحالية غير صحيحة';
            } elseif ($new_password !== $confirm_password) {
                $errors[] = 'كلمات المرور غير متطابقة';
            } elseif (strlen($new_password) < 6) {
                $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
            } else {
                $hashed = md5($new_password);
                $password_update = ", password = '$hashed'";
            }
        }
        
        if (empty($errors)) {
            $conn->query("UPDATE users SET name = '$admin_name', email = '$admin_email' $password_update WHERE id = $user_id");
            $_SESSION['user_name'] = $admin_name;
            $_SESSION['user_email'] = $admin_email;
            showMessage('تم تحديث معلومات الحساب بنجاح');
        }
    }
}

$admin_query = "SELECT * FROM users WHERE id = {$_SESSION['user_id']}";
$admin = $conn->query($admin_query)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .logo-preview {
            max-width: 200px;
            max-height: 100px;
            margin: 15px 0;
            border: 2px dashed var(--border-color);
            padding: 10px;
            border-radius: 8px;
        }
        .favicon-preview {
            width: 32px;
            height: 32px;
            margin: 10px 0;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--border-color);
        }
        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: var(--text-color);
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            font-weight: 600;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>⚙️ الإعدادات</h1>
            </div>
            
            <?php displayMessage(); ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="tabs">
                <button class="tab active" onclick="switchTab('store')">🏪 إعدادات المتجر</button>
                <button class="tab" onclick="switchTab('admin')">👤 حسابي</button>
                <button class="tab" onclick="switchTab('system')">💻 معلومات النظام</button>
            </div>
            
            <!-- تبويب إعدادات المتجر -->
            <div id="store-tab" class="tab-content active">
                <div class="admin-card">
                    <h3>المعلومات الأساسية</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>اسم المتجر *</label>
                                <input type="text" name="site_name" class="form-control" value="<?php echo $settings['site_name']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>رابط المتجر *</label>
                                <input type="url" name="site_url" class="form-control" value="<?php echo $settings['site_url']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>البريد الإلكتروني *</label>
                                <input type="email" name="site_email" class="form-control" value="<?php echo $settings['site_email']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>رقم الهاتف *</label>
                                <input type="tel" name="site_phone" class="form-control" value="<?php echo $settings['site_phone']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>العملة *</label>
                                <input type="text" name="currency" class="form-control" value="<?php echo $settings['currency']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>العنوان</label>
                            <textarea name="site_address" class="form-control" rows="2"><?php echo $settings['site_address']; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>وصف المتجر</label>
                            <textarea name="site_description" class="form-control" rows="3"><?php echo $settings['site_description'] ?? ''; ?></textarea>
                            <small>يستخدم في محركات البحث (SEO)</small>
                        </div>
                        
                        <h3 style="margin-top: 40px;">الشعار والصور</h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>شعار المتجر (Logo)</label>
                                <?php if ($settings['logo']): ?>
                                    <img src="../<?php echo $settings['logo']; ?>" class="logo-preview" id="current-logo">
                                <?php endif; ?>
                                <input type="file" name="logo" class="form-control" accept="image/*" onchange="previewLogo(this)">
                                <small>الحجم المفضل: 200x60 بكسل</small>
                            </div>
                            
                            <div class="form-group">
                                <label>أيقونة المتصفح (Favicon)</label>
                                <?php if ($settings['favicon']): ?>
                                    <img src="../<?php echo $settings['favicon']; ?>" class="favicon-preview">
                                <?php endif; ?>
                                <input type="file" name="favicon" class="form-control" accept="image/*">
                                <small>الحجم المفضل: 32x32 بكسل (.ico أو .png)</small>
                            </div>
                        </div>
                        
                        <h3 style="margin-top: 40px;">صورة البانر الرئيسي</h3>
                        
                        <div class="form-group">
                            <label>صورة البانر</label>
                            <?php if ($settings['banner_image']): ?>
                                <img src="../<?php echo $settings['banner_image']; ?>" style="max-width: 100%; max-height: 200px; margin: 15px 0; border-radius: 8px; border: 2px dashed var(--border-color); padding: 10px;">
                            <?php endif; ?>
                            <input type="file" name="banner_image" class="form-control" accept="image/*" onchange="previewBanner(this)">
                            <small>الحجم المفضل: 1920x600 بكسل</small>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>عنوان البانر</label>
                                <input type="text" name="banner_title" class="form-control" value="<?php echo $settings['banner_title'] ?? 'مرحباً بك في متجرنا'; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>العنوان الفرعي للبانر</label>
                                <input type="text" name="banner_subtitle" class="form-control" value="<?php echo $settings['banner_subtitle'] ?? 'أفضل المنتجات بأفضل الأسعار'; ?>">
                            </div>
                        </div>
                        
                        <h3 style="margin-top: 40px;">وسائل التواصل الاجتماعي</h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>فيسبوك</label>
                                <input type="url" name="facebook" class="form-control" value="<?php echo $settings['facebook'] ?? ''; ?>" placeholder="https://facebook.com/yourpage">
                            </div>
                            
                            <div class="form-group">
                                <label>تويتر</label>
                                <input type="url" name="twitter" class="form-control" value="<?php echo $settings['twitter'] ?? ''; ?>" placeholder="https://twitter.com/yourpage">
                            </div>
                            
                            <div class="form-group">
                                <label>انستقرام</label>
                                <input type="url" name="instagram" class="form-control" value="<?php echo $settings['instagram'] ?? ''; ?>" placeholder="https://instagram.com/yourpage">
                            </div>
                            
                            <div class="form-group">
                                <label>واتساب</label>
                                <input type="tel" name="whatsapp" class="form-control" value="<?php echo $settings['whatsapp'] ?? ''; ?>" placeholder="966500000000">
                            </div>
                        </div>
                        
                        <button type="submit" name="update_store" class="btn btn-primary" style="margin-top: 30px;">💾 حفظ إعدادات المتجر</button>
                    </form>
                </div>
            </div>
            
            <!-- تبويب حساب المدير -->
            <div id="admin-tab" class="tab-content">
                <div class="admin-card">
                    <h3>معلومات الحساب</h3>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>الاسم</label>
                                <input type="text" name="admin_name" class="form-control" value="<?php echo $admin['name']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>البريد الإلكتروني</label>
                                <input type="email" name="admin_email" class="form-control" value="<?php echo $admin['email']; ?>" required>
                            </div>
                        </div>
                        
                        <h3 style="margin-top: 40px;">تغيير كلمة المرور</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>كلمة المرور الحالية</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>كلمة المرور الجديدة</label>
                                <input type="password" name="new_password" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>تأكيد كلمة المرور</label>
                                <input type="password" name="confirm_password" class="form-control">
                            </div>
                        </div>
                        
                        <button type="submit" name="update_admin" class="btn btn-primary" style="margin-top: 30px;">💾 حفظ التغييرات</button>
                    </form>
                </div>
            </div>
            
            <!-- تبويب معلومات النظام -->
            <div id="system-tab" class="tab-content">
                <div class="admin-card">
                    <h3>معلومات النظام</h3>
                    <table class="admin-table">
                        <tr>
                            <td style="width: 30%;"><strong>اسم المتجر:</strong></td>
                            <td><?php echo $settings['site_name']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>رابط المتجر:</strong></td>
                            <td><?php echo $settings['site_url']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>العملة:</strong></td>
                            <td><?php echo $settings['currency']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>إصدار PHP:</strong></td>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <td><strong>قاعدة البيانات:</strong></td>
                            <td><?php echo DB_NAME; ?></td>
                        </tr>
                        <tr>
                            <td><strong>إصدار MySQL:</strong></td>
                            <td><?php echo $conn->server_info; ?></td>
                        </tr>
                        <tr>
                            <td><strong>حجم قاعدة البيانات:</strong></td>
                            <td>
                                <?php 
                                $size_query = "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size 
                                              FROM information_schema.TABLES 
                                              WHERE table_schema = '" . DB_NAME . "'";
                                $size = $conn->query($size_query)->fetch_assoc()['size'];
                                echo $size . ' MB';
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="admin-card">
                    <h3>إحصائيات عامة</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-info">
                                <h3><?php echo $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c']; ?></h3>
                                <p>إجمالي الطلبات</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">🛍️</div>
                            <div class="stat-info">
                                <h3><?php echo $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c']; ?></h3>
                                <p>المنتجات</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-info">
                                <h3><?php echo $conn->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c']; ?></h3>
                                <p>العملاء</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">📁</div>
                            <div class="stat-info">
                                <h3><?php echo $conn->query("SELECT COUNT(*) as c FROM categories")->fetch_assoc()['c']; ?></h3>
                                <p>الفئات</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function switchTab(tabName) {
            // إخفاء جميع التبويبات
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // إظهار التبويب المحدد
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }
        
        function previewLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.getElementById('current-logo');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.id = 'current-logo';
                        preview.className = 'logo-preview';
                        input.parentElement.insertBefore(preview, input);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        function previewBanner(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = input.previousElementSibling;
                    if (!preview || preview.tagName !== 'IMG') {
                        preview = document.createElement('img');
                        preview.style.cssText = 'max-width: 100%; max-height: 200px; margin: 15px 0; border-radius: 8px; border: 2px dashed var(--border-color); padding: 10px;';
                        input.parentElement.insertBefore(preview, input);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>