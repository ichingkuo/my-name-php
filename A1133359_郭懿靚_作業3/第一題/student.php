<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    echo "你沒有權限觀看此頁面！";
    header("Refresh:2; url=login.php");
    exit;
}
?>

<h1>學生專區</h1>
<p>歡迎，<?php echo $_SESSION['username']; ?> 同學！</p>
<a href="logout.php">登出</a>