<?php
require_once 'db.php';
header('Content-Type: application/json');
$res = $conn->query("SELECT no, email FROM emails ORDER BY no");
$list = [];
while ($row = $res->fetch_assoc()) $list[] = $row;
echo json_encode($list);