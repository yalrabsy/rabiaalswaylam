<?php
// إعدادات الاتصال بقاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce_store');
// الاتصال بقاعدة البيانات
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("خطأ في الاتصال: " . $e->getMessage());
}

// جلب إعدادات المتجر من قاعدة البيانات
$settings_query = "SELECT * FROM settings WHERE id = 1 LIMIT 1";
$settings_result = $conn->query($settings_query);

if ($settings_result && $settings_result->num_rows > 0) {
    $site_settings = $settings_result->fetch_assoc();
    define('SITE_NAME', $site_settings['site_name']);
    define('SITE_URL', $site_settings['site_url']);
    define('SITE_EMAIL', $site_settings['site_email']);
    define('SITE_PHONE', $site_settings['site_phone']);
    define('SITE_ADDRESS', $site_settings['site_address']);
    define('SITE_LOGO', $site_settings['logo']);
    define('SITE_FAVICON', $site_settings['favicon']);
    define('CURRENCY', $site_settings['currency']);
} else {
    // إعدادات افتراضية في حالة عدم وجود الجدول
    define('SITE_NAME', 'متجري الإلكتروني');
    define('SITE_URL', 'http://localhost/store');
    define('SITE_EMAIL', 'info@store.com');
    define('SITE_PHONE', '0500000000');
    define('SITE_ADDRESS', 'الرياض، السعودية');
    define('SITE_LOGO', '');
    define('SITE_FAVICON', '');
    define('CURRENCY', 'ر.س');
}


// بدء الجلسة
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// دالة للتحقق من تسجيل الدخول
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// دالة للتحقق من صلاحية المدير
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// دالة لإعادة التوجيه
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// دالة لتنظيف المدخلات
function cleanInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// دالة لعرض الرسائل
function showMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'success';
        $class = $type === 'success' ? 'success' : 'error';
        echo "<div class='message {$class}'>{$_SESSION['message']}</div>";
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}
?>