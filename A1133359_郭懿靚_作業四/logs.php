<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>寄送紀錄</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        .success { color: green; }
        .failed { color: red; }
    </style>
</head>
<body>
<h1>📜 郵件寄送紀錄</h1>
<a href="index.php">← 返回寄送系統</a> | <a href="smtp.php">⚙ SMTP 設定</a>
<table>
    <thead>
        <tr><th>ID</th><th>任務ID</th><th>Email</th><th>狀態</th><th>錯誤訊息</th><th>發送時間</th></tr>
    </thead>
    <tbody>
        <?php
        $result = $conn->query("SELECT * FROM send_logs ORDER BY id DESC LIMIT 200");
        while ($row = $result->fetch_assoc()):
            $statusClass = $row['status'] == 'success' ? 'success' : 'failed';
        ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['job_id'] ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td class="<?= $statusClass ?>"><?= $row['status'] ?></td>
            <td><?= htmlspecialchars($row['error_msg']) ?></td>
            <td><?= $row['sent_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</body>
</html>