<?php
require_once 'db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success'=>false, 'message'=>'Email 格式無效']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO emails (email) VALUES (?)");
    $stmt->bind_param("s", $email);
    if ($stmt->execute()) {
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false, 'message'=> ($conn->errno==1062 ? 'Email 已存在' : '資料庫錯誤')]);
    }
    $stmt->close();
} else {
    echo json_encode(['success'=>false, 'message'=>'無效請求']);
}