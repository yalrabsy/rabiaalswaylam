<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $phone = cleanInput($_POST['phone']);
    $address = cleanInput($_POST['address']);
    $city = cleanInput($_POST['city']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($name)) {
        $errors[] = 'الاسم مطلوب';
    }
    
    // تحديث كلمة المرور إذا تم إدخالها
    $password_update = '';
    if (!empty($new_password)) {
        if (md5($current_password) !== $user['password']) {
            $errors[] = 'كلمة المرور الحالية غير صحيحة';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'كلمات المرور الجديدة غير متطابقة';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        } else {
            $hashed_password = md5($new_password);
            $password_update = ", password = '$hashed_password'";
        }
    }
    
    if (empty($errors)) {
        $update_query = "UPDATE users SET 
                        name = '$name',
                        phone = '$phone',
                        address = '$address',
                        city = '$city'
                        $password_update
                        WHERE id = $user_id";
        
        if ($conn->query($update_query)) {
            $_SESSION['user_name'] = $name;
            showMessage('تم تحديث بياناتك بنجاح');
            redirect('profile.php');
        } else {
            $errors[] = 'حدث خطأ أثناء التحديث';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-page {
            padding: 60px 0;
            background: #f8fafc;
        }
        .profile-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        .profile-sidebar {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: fit-content;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin: 0 auto 20px;
        }
        .profile-menu {
            list-style: none;
            margin-top: 30px;
        }
        .profile-menu li {
            margin-bottom: 10px;
        }
        .profile-menu a {
            display: block;
            padding: 12px 15px;
            color: var(--dark-color);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .profile-menu a:hover,
        .profile-menu a.active {
            background: var(--light-color);
            color: var(--primary-color);
        }
        .profile-content {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="profile-page">
        <div class="container">
            <div class="profile-layout">
                <aside class="profile-sidebar">
                    <div class="profile-avatar">
                        <?php echo mb_substr($user['name'], 0, 1); ?>
                    </div>
                    <h3 style="text-align: center;"><?php echo $user['name']; ?></h3>
                    <p style="text-align: center; color: #64748b;"><?php echo $user['email']; ?></p>
                    
                    <ul class="profile-menu">
                        <li><a href="profile.php" class="active">معلومات الحساب</a></li>
                        <li><a href="orders.php">طلباتي</a></li>
                        <li><a href="cart.php">سلة التسوق</a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/index.php">لوحة التحكم</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">تسجيل الخروج</a></li>
                    </ul>
                </aside>
                
                <div class="profile-content">
                    <h2>معلومات الحساب</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="message error">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <h3>المعلومات الشخصية</h3>
                        <div class="form-grid" style="grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px;">
                            <div class="form-group">
                                <label>الاسم الكامل *</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $user['name']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>البريد الإلكتروني</label>
                                <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                                <small>لا يمكن تعديل البريد الإلكتروني</small>
                            </div>
                            
                            <div class="form-group">
                                <label>رقم الجوال</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone']; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>المدينة</label>
                                <input type="text" name="city" class="form-control" value="<?php echo $user['city']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>العنوان</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo $user['address']; ?></textarea>
                        </div>
                        
                        <h3 style="margin-top: 40px;">تغيير كلمة المرور</h3>
                        <div class="form-grid" style="grid-template-columns: repeat(3, 1fr); gap: 20px;">
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
                        
                        <button type="submit" class="btn btn-primary" style="margin-top: 30px;">حفظ التعديلات</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>