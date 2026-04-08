<html>
<body>
<title>登入錯誤頁面</title>
<?php
$fID="ruby";
$fPWD="123";
if(isset($_POST["uID"]) && isset($_POST["uPWD"])){
    $uID=$_POST["uID"];
    $uPWD=$_POST["uPWD"];

    if($fID==$uID  && $fPWD==$uPWD){
        header("location: form.php");
    }else{
        echo"帳號或密碼輸入失敗";
        header("Refresh2; url=login.php");
    }
}
?>

</body>
</html>