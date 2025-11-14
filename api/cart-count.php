<?php
require_once '../config.php';

header('Content-Type: application/json');

$count = 0;

if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    $count = $data['total'] ?? 0;
}

echo json_encode(['count' => $count]);
?>