<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = cleanInput($_POST['phone']);
    
    // التحقق من البيانات
    if (empty($name)) $errors[] = 'الاسم مطلوب';
    if (empty($email)) $errors[] = 'البريد الإلكتروني مطلوب';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'البريد الإلكتروني غير صالح';
    if (empty($password)) $errors[] = 'كلمة المرور مطلوبة';
    if (strlen($password) < 6) $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    if ($password !== $confirm_password) $errors[] = 'كلمات المرور غير متطابقة';
    
    // التحقق من البريد الإلكتروني
    if (empty($errors)) {
        $check_email = "SELECT id FROM users WHERE email = '$email'";
        $result = $conn->query($check_email);
        
        if ($result->num_rows > 0) {
            $errors[] = 'البريد الإلكتروني مسجل مسبقاً';
        }
    }
    
    // إذا لم يكن هناك أخطاء، قم بالتسجيل
    if (empty($errors)) {
        $hashed_password = md5($password);
        $insert_query = "INSERT INTO users (name, email, password, phone, role) 
                        VALUES ('$name', '$email', '$hashed_password', '$phone', 'customer')";
        
        if ($conn->query($insert_query)) {
            showMessage('تم التسجيل بنجاح! يمكنك الآن تسجيل الدخول');
            redirect('login.php');
        } else {
            $errors[] = 'حدث خطأ أثناء التسجيل';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-page {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 0;
        }
        .auth-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }
        .auth-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .error-messages {
            background: #fee;
            border: 1px solid #fcc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .error-messages li {
            color: #c00;
            margin-bottom: 5px;
        }
        .auth-footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <h2>إنشاء حساب جديد</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label>الاسم الكامل *</label>
                    <input type="text" name="name" class="form-control" 
                           value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>البريد الإلكتروني *</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>رقم الجوال</label>
                    <input type="tel" name="phone" class="form-control" 
                           value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>كلمة المرور *</label>
                    <input type="password" name="password" class="form-control" required>
                    <small>يجب أن تكون 6 أحرف على الأقل</small>
                </div>
                
                <div class="form-group">
                    <label>تأكيد كلمة المرور *</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">إنشاء الحساب</button>
            </form>
            
            <div class="auth-footer">
                <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
                <p><a href="index.php">العودة للرئيسية</a></p>
            </div>
        </div>
    </div>
</body>
</html>