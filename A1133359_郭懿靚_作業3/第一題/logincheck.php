<?php
session_start();

if(empty($_POST)){
    header("Location: login.php");
    exit;
}

$uID=$_POST["uID"];
$uPWD=$_POST["uPWD"];

$date=strtotime("+3600seconds", time());


if($uID== 'student' && $uPWD =='123'){
    $_SESSION['role'] = 'student';
    $_SESSION['username'] = $uID;
    setcookie('save_id',$uID, time() +3600);
    header('Location: student.php');
    exit;
}else if($uID == 'teacher' && $uPWD == '123') {
    $_SESSION['role'] = 'teacher';
    $_SESSION['username'] = $uID;
    setcookie('saved_id', $uID, time() + 3600);
    header('Location: teacher.php');
    exit;

}else if($uID == 'admin' && $uPWD == '123456') {
    $_SESSION['role'] = 'admin';
    $_SESSION['username'] = $uID;
    setcookie('saved_id', $uID, time() + 3600);
    header('Location: admin.php');
    exit;
}else{
    echo "帳號或密碼錯誤，3秒後返回頁首";
     header("Refresh:3; url=login.php");
}


     