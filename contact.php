<?php
require_once 'config.php';

$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $subject = cleanInput($_POST['subject']);
    $message = cleanInput($_POST['message']);
    
    // هنا يمكنك إرسال البريد الإلكتروني أو حفظ الرسالة في قاعدة البيانات
    // mail($to, $subject, $message, $headers);
    
    $success = true;
    showMessage('تم إرسال رسالتك بنجاح! سنتواصل معك قريباً');
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اتصل بنا - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .contact-page {
            padding: 60px 0;
        }
        .contact-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        .contact-info {
            background: linear-gradient(135deg, var(--primary-color), #1e40af);
            color: white;
            padding: 50px;
            border-radius: 12px;
        }
        .contact-info h2 {
            font-size: 32px;
            margin-bottom: 30px;
        }
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-icon {
            font-size: 32px;
        }
        .info-content h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        .contact-form {
            background: white;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .contact-form h2 {
            font-size: 32px;
            margin-bottom: 30px;
            color: var(--primary-color);
        }
        .map-section {
            margin-top: 60px;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .contact-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="contact-page">
        <div class="container">
            <h1 style="text-align: center; font-size: 48px; margin-bottom: 50px;">اتصل بنا</h1>
            
            <div class="contact-layout">
                <div class="contact-info">
                    <h2>تواصل معنا</h2>
                    <p style="margin-bottom: 30px;">نحن هنا للإجابة على جميع استفساراتك ومساعدتك</p>
                    
                    <div class="info-item">
                        <div class="info-icon">📍</div>
                        <div class="info-content">
                            <h3>العنوان</h3>
                            <p>الرياض، حي الملك فهد<br>المملكة العربية السعودية</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">📞</div>
                        <div class="info-content">
                            <h3>الهاتف</h3>
                            <p>+966 50 000 0000<br>+966 11 000 0000</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">📧</div>
                        <div class="info-content">
                            <h3>البريد الإلكتروني</h3>
                            <p>info@store.com<br>support@store.com</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">🕐</div>
                        <div class="info-content">
                            <h3>ساعات العمل</h3>
                            <p>السبت - الخميس: 9:00 ص - 6:00 م<br>الجمعة: مغلق</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h2>أرسل لنا رسالة</h2>
                    
                    <?php if ($success): ?>
                        <div class="message success">
                            تم إرسال رسالتك بنجاح! سنتواصل معك قريباً
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>الاسم *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>البريد الإلكتروني *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>الموضوع *</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>الرسالة *</label>
                            <textarea name="message" class="form-control" rows="6" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">إرسال الرسالة</button>
                    </form>
                </div>
            </div>
            
            <div class="map-section">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3624.3956748451285!2d46.6752957!3d24.7135517!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e2f03f87b57c93d%3A0x5c5c8c8c8c8c8c8c!2sRiyadh%20Saudi%20Arabia!5e0!3m2!1sen!2s!4v1234567890"
                    width="100%" 
                    height="100%" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>