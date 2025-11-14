<?php
require_once 'config.php';

// تدمير الجلسة
session_destroy();

// مسح جميع المتغيرات
$_SESSION = array();

// حذف كوكيز الجلسة
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// إعادة التوجيه للصفحة الرئيسية
redirect('index.php');
?>