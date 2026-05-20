<?php
require_once 'db.php';
$emails = [];
$res = $conn->query("SELECT no, email FROM emails ORDER BY no");
while ($row = $res->fetch_assoc()) $emails[] = $row;
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="utf-8">
<title>郵件寄送系統</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { font-family: 'Segoe UI', Arial, sans-serif; margin: 30px; background: #f5f7fa; }
h1 { color: #333; }
.flex { display: flex; gap: 30px; flex-wrap: wrap; }
.box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); flex: 1; min-width: 300px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #f0f0f0; }
button { background: #007bff; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
button:disabled { background: #aaa; cursor: not-allowed; }
input, textarea, select { padding: 6px; border-radius: 4px; border: 1px solid #ccc; }
.progress { margin-top: 15px; display: none; }
</style>
</head>
<body>
<h1>📧 郵件寄送系統</h1>
<div class="flex">
  <div class="box">
    <h2>郵件地址管理</h2>
    <form id="addEmailForm">
      <input type="email" name="email" placeholder="輸入 Email" required>
      <button type="submit">新增</button>
    </form>
    <table>
      <thead><tr><th>編號</th><th>Email</th></tr></thead>
      <tbody id="emailTableBody">
        <?php foreach($emails as $e): ?>
        <tr><td><?= $e['no'] ?></td><td><?= htmlspecialchars($e['email']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="box">
    <h2>寄送設定</h2>
    <div>
      <label>發送模式：</label>
      <input type="radio" name="mode" value="all" checked> 全部寄送
      <input type="radio" name="mode" value="random"> 隨機寄送
      <span id="randomRange" style="display:none;">
        最小 <input type="number" id="random_min" value="1" min="1" step="1" style="width:70px"> 封，
        最大 <input type="number" id="random_max" value="5" min="1" step="1" style="width:70px"> 封
      </span>
    </div>
    <div style="margin-top:12px;">
      <label>間隔設定：</label>
      <input type="radio" name="interval_type" value="fixed" checked> 固定
      <input type="number" id="fixed_sec" value="3" min="0" step="1" style="width:70px"> 秒
      <input type="radio" name="interval_type" value="random"> 隨機間隔
      <span id="randomInputs" style="display:none;">
        最小 <input type="number" id="rand_min" value="1" min="1" step="1" style="width:70px"> 秒，
        最大 <input type="number" id="rand_max" value="5" min="1" step="1" style="width:70px"> 秒
      </span>
    </div>
    <div style="margin-top:12px;">
      <label>郵件主旨：</label><br>
      <input type="text" id="subject" value="測試郵件" style="width:100%">
    </div>
    <div style="margin-top:12px;">
      <label>郵件內容（HTML）：</label><br>
      <textarea id="body" rows="6" style="width:100%"><p>這是一封測試郵件。</p></textarea>
    </div>
    <div style="margin-top:16px;">
      <button id="startSendBtn">▶ 開始寄送</button>
    </div>
    <div id="progressArea" class="progress">
      <p>寄送進度：<span id="progressText">0/0</span></p>
      <progress id="progressBar" value="0" max="100" style="width:100%; height:20px;"></progress>
      <p id="statusMsg"></p>
    </div>
  </div>
</div>
<p style="margin-top:20px;"><a href="smtp.php">⚙ SMTP 設定</a> | <a href="logs.php">📋 寄送紀錄</a></p>

<script>
// Email 新增
document.getElementById('addEmailForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const resp = await fetch('add_email.php', { method: 'POST', body: formData });
    const data = await resp.json();
    if (data.success) {
        loadEmailTable();
        e.target.reset();
    } else {
        alert(data.message || '新增失敗');
    }
});

async function loadEmailTable() {
    const resp = await fetch('get_emails.php');
    const emails = await resp.json();
    const tbody = document.getElementById('emailTableBody');
    tbody.innerHTML = emails.map(e => `<tr><td>${e.no}</td><td>${escapeHtml(e.email)}</td></tr>`).join('');
}

function escapeHtml(str) {
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// 模式切換
document.querySelectorAll('input[name="mode"]').forEach(r => {
    r.addEventListener('change', () => {
        const isRandom = r.value === 'random';
        document.getElementById('randomRange').style.display = isRandom ? 'inline' : 'none';
    });
});

// 間隔切換
document.querySelectorAll('input[name="interval_type"]').forEach(r => {
    r.addEventListener('change', () => {
        document.getElementById('fixed_sec').disabled = r.value !== 'fixed';
        document.getElementById('randomInputs').style.display = r.value === 'random' ? 'inline' : 'none';
    });
});

// 隨機數量範圍校驗：保證 max >= min
function validateRandomCount() {
    const minVal = parseInt(document.getElementById('random_min').value, 10);
    const maxVal = parseInt(document.getElementById('random_max').value, 10);
    if (minVal > maxVal) {
        document.getElementById('random_max').value = minVal;
    }
}

document.getElementById('random_min').addEventListener('change', validateRandomCount);
document.getElementById('random_max').addEventListener('change', validateRandomCount);

// 開始寄送
document.getElementById('startSendBtn').addEventListener('click', async function() {
    const btn = this;
    const mode = document.querySelector('input[name="mode"]:checked').value;
    const intervalType = document.querySelector('input[name="interval_type"]:checked').value;
    const params = new URLSearchParams({
        mode,
        interval_type: intervalType,
        subject: document.getElementById('subject').value,
        body: document.getElementById('body').value
    });
    
    if (mode === 'random') {
        let minVal = parseInt(document.getElementById('random_min').value, 10);
        let maxVal = parseInt(document.getElementById('random_max').value, 10);
        if (isNaN(minVal)) minVal = 1;
        if (isNaN(maxVal)) maxVal = 5;
        if (minVal > maxVal) { let tmp = minVal; minVal = maxVal; maxVal = tmp; }
        params.append('random_min', minVal);
        params.append('random_max', maxVal);
    }
    if (intervalType === 'fixed') {
        params.append('fixed_sec', document.getElementById('fixed_sec').value);
    } else {
        let minSec = parseInt(document.getElementById('rand_min').value, 10);
        let maxSec = parseInt(document.getElementById('rand_max').value, 10);
        if (isNaN(minSec)) minSec = 1;
        if (isNaN(maxSec)) maxSec = 5;
        if (minSec > maxSec) { let tmp = minSec; minSec = maxSec; maxSec = tmp; }
        params.append('rand_min', minSec);
        params.append('rand_max', maxSec);
    }
    if (!params.get('subject')) { alert('請輸入主旨'); return; }

    btn.disabled = true;
    btn.textContent = '發送中...';
    const progArea = document.getElementById('progressArea');
    progArea.style.display = 'block';
    document.getElementById('progressText').textContent = '準備中...';
    document.getElementById('progressBar').value = 0;
    document.getElementById('statusMsg').textContent = '';

    try {
        const resp = await fetch('send.php', { method: 'POST', body: params });
        const data = await resp.json();
        if (!data.success) {
            alert(data.message || '啟動失敗');
            resetBtn();
            return;
        }
        const jobId = data.job_id;
        const interval = setInterval(async () => {
            const progResp = await fetch(`progress.php?job_id=${jobId}`);
            const prog = await progResp.json();
            document.getElementById('progressText').textContent = `${prog.sent}/${prog.total}`;
            document.getElementById('progressBar').value = prog.total ? Math.round(prog.sent/prog.total*100) : 0;
            if (prog.status === 'completed') {
                clearInterval(interval);
                document.getElementById('statusMsg').textContent = '✅ 發送完成！';
                resetBtn();
            } else if (prog.status === 'error') {
                clearInterval(interval);
                document.getElementById('statusMsg').textContent = '❌ 發送過程發生錯誤';
                resetBtn();
            }
        }, 1000);
    } catch (err) {
        alert('請求失敗：' + err);
        resetBtn();
    }

    function resetBtn() {
        btn.disabled = false;
        btn.textContent = '▶ 開始寄送';
    }
});
</script>
</body>
</html>