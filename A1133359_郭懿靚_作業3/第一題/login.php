<?php
session_start();


if (isset($_COOKIE['saved_id'])) {
    echo "上次登入的帳號：" . $_COOKIE['saved_id'];
    echo " <a href='cookiedel.php'>清除記錄</a><br><br>";
}
?>



<form action="logincheck.php" method="POST">
    ID: <input type="text" name="uID"><br>
    Password: <input type="password" name="uPWD"><br>
    <input type="submit" value="登入">
</form>

<?php
echo "目前時間戳記：" . time();
?>