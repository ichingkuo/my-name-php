<?php
require_once 'db.php';

// 處理儲存設定
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $stmt = $conn->prepare("UPDATE smtp_config SET host=?, port=?, username=?, password=?, encryption=?, from_email=?, from_name=? WHERE id=1");
    $stmt->bind_param("sisssss", $_POST['host'], $_POST['port'], $_POST['username'], $_POST['password'], $_POST['encryption'], $_POST['from_email'], $_POST['from_name']);
    $msg = $stmt->execute() ? '✅ 設定已更新' : '❌ 更新失敗';
    $stmt->close();
}

// 處理測試寄信
$testMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $testEmail = trim($_POST['test_email']);
    if (filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        // 取得當前 SMTP 設定
        $cfg = $conn->query("SELECT * FROM smtp_config WHERE id=1")->fetch_assoc();
        if ($cfg) {
            $result = smtp_send_mail($testEmail, 'SMTP 測試信件', '<h2>🎉 測試成功</h2><p>您的 SMTP 設定正確，可以正常寄信。</p>', $cfg);
            $testMsg = $result['success'] ? '✅ 測試信已發送，請查收 ' . htmlspecialchars($testEmail) : '❌ 測試失敗：' . htmlspecialchars($result['error']);
        } else {
            $testMsg = '❌ 找不到 SMTP 設定，請先儲存設定';
        }
    } else {
        $testMsg = '❌ 請輸入有效的 Email 地址';
    }
}

// 讀取目前設定
$cfg = $conn->query("SELECT * FROM smtp_config WHERE id=1")->fetch_assoc();
if (!$cfg) {
    // 若無資料則插入預設空白記錄（避免錯誤）
    $conn->query("INSERT INTO smtp_config (id, host, port, username, password, encryption, from_email, from_name) VALUES (1, 'smtp.gmail.com', 587, '', '', 'tls', '', '')");
    $cfg = $conn->query("SELECT * FROM smtp_config WHERE id=1")->fetch_assoc();
}

/**
 * 原生 PHP SMTP 寄信函數（支援 TLS/SSL）
 * @param string $to          收件人
 * @param string $subject     主旨
 * @param string $body        HTML 內容
 * @param array  $smtpConfig  SMTP 設定 (host, port, username, password, encryption, from_email, from_name)
 * @return array ['success'=>bool, 'error'=>string]
 */
function smtp_send_mail($to, $subject, $body, $smtpConfig) {
    $host = $smtpConfig['host'];
    $port = $smtpConfig['port'];
    $username = $smtpConfig['username'];
    $password = $smtpConfig['password'];
    $encryption = $smtpConfig['encryption'];
    $fromEmail = $smtpConfig['from_email'];
    $fromName = $smtpConfig['from_name'];

    // 建立 socket 連線
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
    // 跳過後續的 250 行
    while (substr($response, 3, 1) == '-') {
        $response = fgets($socket, 1024);
    }

    // STARTTLS (如果加密是 tls)
    if ($encryption == 'tls') {
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 1024);
        if (substr($response, 0, 3) != '220') {
            return ['success' => false, 'error' => "STARTTLS 失敗: $response"];
        }
        // 啟用加密
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        // 重新 EHLO
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
    // 發送 username (base64)
    fputs($socket, base64_encode($username) . "\r\n");
    $response = fgets($socket, 1024);
    if (substr($response, 0, 3) != '334') {
        return ['success' => false, 'error' => "Username 錯誤: $response"];
    }
    // 發送 password (base64)
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

    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return ['success' => true, 'error' => ''];
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMTP 設定 - 郵件系統</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: #f0f2f5;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            margin-top: 0;
            color: #1a73e8;
        }
        label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
            margin-top: 12px;
        }
        input, select {
            padding: 8px;
            margin-top: 8px;
            width: calc(100% - 130px);
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 20px;
            margin-right: 10px;
        }
        button:hover {
            background: #0d5bbf;
        }
        .msg {
            padding: 10px;
            margin: 15px 0;
            border-radius: 6px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        hr {
            margin: 25px 0;
        }
        .note {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #1a73e8;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>⚙ SMTP 設定</h1>

    <?php if ($msg): ?>
        <div class="msg success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="post">
        <label>主機：</label>
        <input type="text" name="host" value="<?= htmlspecialchars($cfg['host']) ?>" required>
        <div class="note">例如：smtp.gmail.com</div>

        <label>埠號：</label>
        <input type="number" name="port" value="<?= $cfg['port'] ?>" required>
        <div class="note">Gmail 使用 587 (TLS) 或 465 (SSL)</div>

        <label>加密：</label>
        <select name="encryption">
            <option value="tls" <?= $cfg['encryption'] == 'tls' ? 'selected' : '' ?>>TLS</option>
            <option value="ssl" <?= $cfg['encryption'] == 'ssl' ? 'selected' : '' ?>>SSL</option>
            <option value="none" <?= $cfg['encryption'] == 'none' ? 'selected' : '' ?>>無</option>
        </select>

        <label>帳號：</label>
        <input type="text" name="username" value="<?= htmlspecialchars($cfg['username']) ?>" required>
        <div class="note">完整的 Gmail 信箱（例如 yourname@gmail.com）</div>

        <label>密碼：</label>
        <input type="password" name="password" value="<?= htmlspecialchars($cfg['password']) ?>" required>
        <div class="note">⚠️ 若使用 Gmail，請使用「應用程式密碼」（不是登入密碼）</div>

        <label>寄件人 Email：</label>
        <input type="email" name="from_email" value="<?= htmlspecialchars($cfg['from_email']) ?>" required>

        <label>寄件人名稱：</label>
        <input type="text" name="from_name" value="<?= htmlspecialchars($cfg['from_name']) ?>">
        <div class="note">例如：客服中心</div>

        <button type="submit" name="save">💾 儲存設定</button>
    </form>

    <hr>

    <h2>🔍 測試連線與寄信</h2>
    <?php if ($testMsg): ?>
        <div class="msg <?= strpos($testMsg, '✅') !== false ? 'success' : 'error' ?>">
            <?= $testMsg ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <label>測試 Email：</label>
        <input type="email" name="test_email" placeholder="例如：a0903500969@gmail.com" required>
        <button type="submit">📨 發送測試信</button>
    </form>

    <a href="index.php" class="back-link">← 返回寄送系統</a>
    <a href="logs.php" class="back-link" style="margin-left: 20px;">📋 查看寄送紀錄</a>
</div>
</body>
</html>