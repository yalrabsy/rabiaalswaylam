<?php
// ملف لإضافته في <head> جميع الصفحات
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?php echo defined('SITE_DESCRIPTION') ? SITE_DESCRIPTION : SITE_NAME; ?>">
<meta name="keywords" content="متجر إلكتروني، تسوق أونلاين، منتجات">
<meta name="author" content="<?php echo SITE_NAME; ?>">

<!-- Open Graph Meta Tags -->
<meta property="og:title" content="<?php echo SITE_NAME; ?>">
<meta property="og:description" content="<?php echo defined('SITE_DESCRIPTION') ? SITE_DESCRIPTION : 'أفضل متجر إلكتروني'; ?>">
<meta property="og:url" content="<?php echo SITE_URL; ?>">
<meta property="og:type" content="website">

<!-- Favicon -->
<?php if (defined('SITE_FAVICON') && SITE_FAVICON): ?>
    <link rel="icon" type="image/png" href="<?php echo SITE_FAVICON; ?>">
    <link rel="shortcut icon" href="<?php echo SITE_FAVICON; ?>">
<?php else: ?>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
<?php endif; ?>

<!-- Apple Touch Icon -->
<link rel="apple-touch-icon" href="<?php echo SITE_LOGO ?: 'assets/images/logo.png'; ?>"><?php
$site_settings_query = "SELECT * FROM settings WHERE id = 1";
$site_settings_result = $conn->query($site_settings_query);
if ($site_settings_result && $site_settings_result->num_rows > 0) {
    $site_config = $site_settings_result->fetch_assoc();
    define('SITE_DESCRIPTION', $site_config['site_description'] ?? '');
}
?>