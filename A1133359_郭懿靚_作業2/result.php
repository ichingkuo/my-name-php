<html>
    <head>
        <title>報名結果</title>
    </head>
    <body>
<center>
    <font size="4">
<?php
echo "您的姓名是:" .$_POST["name"] ."<br/>";
$blood=$_POST["blood"];
$birth=$_POST["birth"];
$gender=$_POST["gender"];//用post重新接收
$identity=$_POST["identity"];
$school=$_POST["school"];
$class=$_POST["class"];
$id=$_POST["id"];
$contact_title=$_POST["contact_title"];
$contact_name=$_POST["contact_name"];
$contact_phone=$_POST["contact_phone"];
$register_type = $_POST["register_type"] ;
$skills = $_POST["skills"];
$parent_name=$_POST["parent_name"];
$relation=$_POST["relation"];
$date=$_POST["date"];

if($blood=="A型"){
    echo "您的血型是:A型<br/>";
}else if($blood=="B型") {
    echo "您的血型是:B型<br/>";
}else if($blood=="O型") {
    echo "您的血型是:O型<br/>";
}else if($blood=="AB型") {
    echo "您的血型是:AB型<br/>";
    }else {
    echo "您的血型是:其他<br/>";
}

echo "您的生日是:" . $birth ."<br/>";

if($gender=="帥哥") {
    echo "您是:帥哥<br/>";
}else{
    echo"您是:美女<br/>";
}

echo "<br>";
if($identity=="國小"){
    echo "您的身分是:國小生<br/>";
}else if($identity=="國中"){
    echo "您的身分是:國中生<br/>";
}else if($identity=="高中"){
    echo "您的身分是:高中生<br/>";
    }else if($identity=="大學"){
    echo"您的身分是:大學生";
} else {
    echo "您的身分是:其他<br/>";
}

echo "您的就讀學校是:" . $school ."<br/>";
echo "您的班級是:" .$class . "<br/>";
echo "您的身分證字號是:" .$id  ."<br/>";
echo "<br>";
echo "您的緊急連絡人稱謂是:" .$contact_title . "<br/>";
echo "您的緊急連絡人姓名是:" .$contact_name . "<br/>";
echo "您的緊急連絡人手機號碼是:" .$contact_phone . "<br/>";


if ($register_type == "新生") {
    echo "您是:新生<br>";
} else if($register_type == "舊生"){
    echo "您是:舊生<br>";
}else if($register_type == "早鳥者"){
    echo "您是:早鳥者<br>";
    }

echo "<br>";
echo "想學習的技巧:<br>";
foreach ($skills as $sk) {
    switch ($sk) {
        case "手沖咖啡":
            echo "手沖咖啡<br>";
            break;
        case "拉花":
            echo "拉花<br>";
            break;
        case "咖啡豆分辨":
            echo "咖啡豆分辨<br>";
            break;
        case "咖啡產地介紹 ":
            echo "咖啡產地介紹<br>";
            break;
        case "其他":
            echo "其他<br>";
            break;
    }
}
echo "<br>";
echo "您的同意人姓名是:" .$parent_name . "<br/>";
echo "您與同意人的關係是:" .$relation . "<br/>";
echo "您的填表日期是:" .$date . "<br/>";    

?>
</font>
</center>
</body>
</html>