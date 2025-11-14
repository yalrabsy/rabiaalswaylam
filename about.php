<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>من نحن - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .about-page {
            padding: 60px 0;
        }
        .about-hero {
            background: linear-gradient(135deg, var(--primary-color), #1e40af);
            color: white;
            padding: 80px 0;
            text-align: center;
            border-radius: 12px;
            margin-bottom: 60px;
        }
        .about-hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-bottom: 60px;
        }
        .about-text h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        .about-text p {
            font-size: 18px;
            line-height: 1.8;
            color: var(--text-color);
            margin-bottom: 15px;
        }
        .about-image img {
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 60px;
        }
        .value-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .value-card:hover {
            transform: translateY(-10px);
        }
        .value-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .value-card h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        .stats-section {
            background: var(--light-color);
            padding: 60px 0;
            margin-top: 60px;
            border-radius: 12px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            text-align: center;
        }
        .stat-item h3 {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .about-content, .values-grid, .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="about-page">
        <div class="container">
            <div class="about-hero">
                <h1>من نحن</h1>
                <p>نحن متجرك الإلكتروني الموثوق لأفضل المنتجات</p>
            </div>
            
            <div class="about-content">
                <div class="about-text">
                    <h2>قصتنا</h2>
                    <p>
                        نحن <?php echo SITE_NAME; ?>، متجر إلكتروني رائد في مجال التجارة الإلكترونية في المملكة العربية السعودية.
                        بدأت رحلتنا بهدف توفير تجربة تسوق فريدة ومريحة لعملائنا.
                    </p>
                    <p>
                        نفخر بتقديم مجموعة واسعة من المنتجات عالية الجودة بأسعار تنافسية، 
                        مع التزامنا الكامل بتوفير أفضل خدمة عملاء وأسرع عملية شحن وتوصيل.
                    </p>
                    <p>
                        فريقنا يعمل بجد لضمان رضاك التام عن كل عملية شراء، 
                        ونسعى دائماً لتطوير خدماتنا وتوسيع نطاق منتجاتنا.
                    </p>
                </div>
                
                <div class="about-image">
                    <img src="assets/images/about-us.jpg" alt="من نحن" style="background: #e2e8f0; min-height: 400px; display: flex; align-items: center; justify-content: center;">
                </div>
            </div>
            
            <h2 style="text-align: center; font-size: 36px; margin-bottom: 40px;">قيمنا</h2>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">🎯</div>
                    <h3>الجودة</h3>
                    <p>نلتزم بتوفير منتجات عالية الجودة من أفضل الموردين</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">⚡</div>
                    <h3>السرعة</h3>
                    <p>شحن سريع وتوصيل في الوقت المحدد لجميع الطلبات</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">💎</div>
                    <h3>الثقة</h3>
                    <p>نبني علاقة ثقة طويلة الأمد مع عملائنا</p>
                </div>
            </div>
            
            <div class="stats-section">
                <div class="stats-grid">
                    <div class="stat-item">
                        <h3>10,000+</h3>
                        <p>عميل سعيد</p>
                    </div>
                    
                    <div class="stat-item">
                        <h3>5,000+</h3>
                        <p>منتج متنوع</p>
                    </div>
                    
                    <div class="stat-item">
                        <h3>50+</h3>
                        <p>مدينة نخدمها</p>
                    </div>
                    
                    <div class="stat-item">
                        <h3>24/7</h3>
                        <p>دعم العملاء</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>