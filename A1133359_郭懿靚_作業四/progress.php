<?php
require_once 'db.php';
header('Content-Type: application/json');
if (!isset($_GET['job_id'])) {
    echo json_encode(['sent'=>0, 'total'=>0, 'status'=>'error']);
    exit;
}
$jobId = intval($_GET['job_id']);
$stmt = $conn->prepare("SELECT total, sent, status FROM send_jobs WHERE id=?");
$stmt->bind_param("i", $jobId);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['sent'=>0, 'total'=>0, 'status'=>'error', 'message'=>'任務不存在']);
}
$stmt->close();