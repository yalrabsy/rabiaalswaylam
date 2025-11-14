<?php
require_once 'config.php';

// إذا كان المستخدم مسجل دخول، إعادة توجيه
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'يرجى إدخال البريد الإلكتروني وكلمة المرور';
    } else {
        // البحث عن المستخدم
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // التحقق من كلمة المرور
            if (md5($password) === $user['password']) {
                // تسجيل الدخول ناجح
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                showMessage('تم تسجيل الدخول بنجاح');
                
                // إعادة التوجيه حسب الدور
                if ($user['role'] === 'admin') {
                    redirect('admin/index.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $error = 'كلمة المرور غير صحيحة';
            }
        } else {
            $error = 'البريد الإلكتروني غير مسجل';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - <?php echo SITE_NAME; ?></title>
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
            max-width: 450px;
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
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c00;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
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
            <h2>تسجيل الدخول</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label>البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">تسجيل الدخول</button>
            </form>
            
            <div class="auth-footer">
                <p>ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a></p>
                <p><a href="index.php">العودة للرئيسية</a></p>
            </div>
        </div>
    </div>
</body>
</html>