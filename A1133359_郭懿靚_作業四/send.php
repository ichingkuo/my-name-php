<?php
require_once 'db.php';
header('Content-Type: application/json');

// ---------- 原生 SMTP 函數（不使用 PHPMailer）----------
function smtp_send_mail($to, $subject, $body, $smtpConfig) {
    $host = $smtpConfig['host'];
    $port = $smtpConfig['port'];
    $username = $smtpConfig['username'];
    $password = $smtpConfig['password'];
    $encryption = $smtpConfig['encryption'];
    $fromEmail = $smtpConfig['from_email'];
    $fromName = $smtpConfig['from_name'];

    $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
    $timeout = 30;
    $errno = 0;
    $errstr = '';
    $socket = @stream_socket_client(
        ($encryption == 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port,
        $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context
    );
    if (!$socket) {
        return ['success' => false, 'error' => "無法連線 SMTP 主機: $errstr ($errno)"];
    }
    stream_set_timeout($socket, $timeout);

    // 讀取歡迎訊息
    $response = fgets($socket, 1024);
    if (substr($response, 0, 3) != '220') {
        return ['success' => false, 'error' => "SMTP 歡迎訊息錯誤: $response"];
    }

    // EHLO
    fputs($socket, "EHLO localhost\r\n");
    $response = fgets($socket, 1024);
    if (substr($response, 0, 3) != '250') {
        return ['success' => false, 'error' => "EHLO 失敗: $response"];
    }
    while (substr($response, 3, 1) == '-') {
        $response = fgets($socket, 1024);
    }

    // STARTTLS
    if ($encryption == 'tls') {
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 1024);
        if (substr($response, 0, 3) != '220') {
            return ['success' => false, 'error' => "STARTTLS 失敗: $response"];
        }
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fputs($socket, "EHLO localhost\r\n");
        $response = fgets($socket, 1024);
        if (substr($response, 0, 3) != '250') {
            return ['success' => false, 'error' => "加密後 EHLO 失敗: $response"];
        }
        while (substr($response, 3, 1) == '-') {
            $response = fgets($socket, 1024);
        }
    }

    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 1024);
    if (substr($response, 0, 3) != '334') {
        return ['success' => false, 'error' => "AUTH LOGIN 不接受: $response"];
    }
    fputs($socket, base64_encode($username) . "\r\n");
    $response = fgets($socket, 1024);
    if (substr($response, 0, 3) != '334') {
        return ['success' => false, 'error' => "Username 錯誤: $response"];
    }
    fputs($socket, base64_encode($password) . "\r\n");
    $response = fgets($socket, 1024);
    if (substr($response, 0, 3) != '235') {
        return ['success' => false, 'error' => "密碼錯誤或信箱未授權: $response"];
    }

    // MAIL FROM
    fputs($socket, "MAIL FROM:<$fromEmail>\r\n");
    $response = fgets($socket, 1024);
    if (substr($response, 0, 3) != '250') {
        return ['success' => false, 'error' => "MAIL FROM 失敗: $response"];
    }

    // RCPT TO
    fputs($socket, "RCPT TO:<$to>\r\n");
    $response = fgets($socket, 1024);
    if (substr($response, 0, 3) != '250') {
        return ['success' => false, 'error' => "RCPT TO 失敗: $response"];
    }

    // DATA
    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 1024);
    if (substr($response, 0, 3) != '354') {
        return ['success' => false, 'error' => "DATA 指令失敗: $response"];
    }

    // 郵件內容
    $message = "MIME-Version: 1.0\r\n";
    $message .= "Content-type: text/html; charset=utf-8\r\n";
    $message .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$fromEmail>\r\n";
    $message .= "To: <$to>\r\n";
    $message .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $message .= "\r\n";
    $message .= $body . "\r\n";
    $message .= ".\r\n";

    fputs($socket, $message);
    $response = fgets($socket, 1024);
    if (substr($response, 0, 3) != '250') {
        return ['success' => false, 'error' => "DATA 內容錯誤: $response"];
    }

    fputs($socket, "QUIT\r\n");
    fclose($socket);
    return ['success' => true, 'error' => ''];
}
// ----------------------------------------------------

// 取得 SMTP 設定
$smtpRes = $conn->query("SELECT * FROM smtp_config WHERE id=1");
if (!$smtpRes || $smtpRes->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'SMTP 尚未設定']);
    exit;
}
$smtp = $smtpRes->fetch_assoc();

// 取得發送名單（支援隨機範圍）
$mode = $_POST['mode'] ?? 'all';
if ($mode === 'random') {
    $min = intval($_POST['random_min'] ?? 1);
    $max = intval($_POST['random_max'] ?? 5);
    if ($min < 1) $min = 1;
    if ($max < $min) $max = $min;
    $count = rand($min, $max);
    $stmt = $conn->prepare("SELECT email FROM emails ORDER BY RAND() LIMIT ?");
    $stmt->bind_param("i", $count);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query("SELECT email FROM emails");
}
$emailList = [];
while ($row = $res->fetch_assoc()) $emailList[] = $row['email'];
$total = count($emailList);
if ($total === 0) {
    echo json_encode(['success' => false, 'message' => '沒有可發送的郵件地址']);
    exit;
}

// 任務鎖（避免同時執行，自動解鎖超過10分鐘的任務）
$runningJob = $conn->query("SELECT id, created_at FROM send_jobs WHERE status='running' LIMIT 1");
if ($runningJob && $runningJob->num_rows > 0) {
    $job = $runningJob->fetch_assoc();
    $created = strtotime($job['created_at']);
    if (time() - $created > 600) {
        $conn->query("UPDATE send_jobs SET status='error' WHERE id=" . intval($job['id']));
    } else {
        echo json_encode(['success' => false, 'message' => '已有發送任務執行中，請稍後再試']);
        exit;
    }
}

// 建立新任務
$stmt = $conn->prepare("INSERT INTO send_jobs (total, sent, status) VALUES (?, 0, 'running')");
$stmt->bind_param("i", $total);
$stmt->execute();
$jobId = $stmt->insert_id;
$stmt->close();

// 先回應前端 job_id，然後繼續背景執行
ignore_user_abort(true);
set_time_limit(0);
ob_end_clean();
header("Connection: close");
header("Content-Type: application/json");
ob_start();
echo json_encode(['success' => true, 'job_id' => $jobId]);
$size = ob_get_length();
header("Content-Length: $size");
ob_end_flush();
flush();
if (session_id()) session_write_close();

// 開始循環發送
$intervalType = $_POST['interval_type'] ?? 'fixed';
$subject = $_POST['subject'] ?? '';
$body = $_POST['body'] ?? '';
foreach ($emailList as $email) {
    $result = smtp_send_mail($email, $subject, $body, $smtp);
    $status = $result['success'] ? 'success' : 'failed';
    $errorMsg = $result['error'] ?? '';
    
    // 記錄發送結果
    $stmtLog = $conn->prepare("INSERT INTO send_logs (job_id, email, status, error_msg) VALUES (?, ?, ?, ?)");
    $stmtLog->bind_param("isss", $jobId, $email, $status, $errorMsg);
    $stmtLog->execute();
    $stmtLog->close();
    
    // 更新進度
    $conn->query("UPDATE send_jobs SET sent = sent + 1 WHERE id = $jobId");
    
    // 間隔等待
    if ($intervalType === 'fixed') {
        $sec = intval($_POST['fixed_sec'] ?? 1);
        if ($sec > 0) sleep($sec);
    } else {
        $minSec = intval($_POST['rand_min'] ?? 1);
        $maxSec = intval($_POST['rand_max'] ?? 5);
        if ($minSec < 1) $minSec = 1;
        if ($maxSec < $minSec) $maxSec = $minSec;
        $sec = rand($minSec, $maxSec);
        sleep($sec);
    }
}
// 標記完成
$conn->query("UPDATE send_jobs SET status='completed' WHERE id=$jobId");