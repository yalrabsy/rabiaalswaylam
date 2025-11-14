<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3><?php echo SITE_NAME; ?></h3>
                <p><?php echo defined('SITE_DESCRIPTION') && SITE_DESCRIPTION ? SITE_DESCRIPTION : 'متجرك الإلكتروني الموثوق لأفضل المنتجات بأفضل الأسعار'; ?></p>
                <div class="social-links">
                    <?php 
                    // جلب روابط التواصل
                    $social_query = "SELECT facebook, twitter, instagram, whatsapp FROM settings WHERE id = 1";
                    $social_result = $conn->query($social_query);
                    if ($social_result && $social_result->num_rows > 0) {
                        $social = $social_result->fetch_assoc();
                        if ($social['facebook']): ?>
                            <a href="<?php echo $social['facebook']; ?>" target="_blank">📘 فيسبوك</a>
                        <?php endif;
                        if ($social['twitter']): ?>
                            <a href="<?php echo $social['twitter']; ?>" target="_blank">🐦 تويتر</a>
                        <?php endif;
                        if ($social['instagram']): ?>
                            <a href="<?php echo $social['instagram']; ?>" target="_blank">📷 انستقرام</a>
                        <?php endif;
                        if ($social['whatsapp']): ?>
                            <a href="https://wa.me/<?php echo $social['whatsapp']; ?>" target="_blank">💬 واتساب</a>
                        <?php endif;
                    }
                    ?>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>روابط سريعة</h3>
                <ul>
                    <li><a href="index.php">الرئيسية</a></li>
                    <li><a href="products.php">المنتجات</a></li>
                    <li><a href="about.php">من نحن</a></li>
                    <li><a href="contact.php">اتصل بنا</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>خدمة العملاء</h3>
                <ul>
                    <li><a href="#">سياسة الخصوصية</a></li>
                    <li><a href="#">الشروط والأحكام</a></li>
                    <li><a href="#">سياسة الإرجاع</a></li>
                    <li><a href="#">الأسئلة الشائعة</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>تواصل معنا</h3>
                <ul>
                    <li>📞 الهاتف: <?php echo defined('SITE_PHONE') ? SITE_PHONE : '0500000000'; ?></li>
                    <li>📧 البريد: <?php echo defined('SITE_EMAIL') ? SITE_EMAIL : 'info@store.com'; ?></li>
                    <li>📍 العنوان: <?php echo defined('SITE_ADDRESS') ? SITE_ADDRESS : 'الرياض، المملكة العربية السعودية'; ?></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</footer>