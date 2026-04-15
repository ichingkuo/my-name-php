<?php
$id = $_GET["Id"] ?? '';
if ($id && isset($_COOKIE[$id]) && is_array($_COOKIE[$id])) {
    foreach ($_COOKIE[$id] as $key => $val) {
        // 將 Cookie 有效期設為過去時間，即可刪除
        setcookie($id."[$key]", "", time() - 3600);
    }
}
header("Location: shoppingcart.php");
exit;
?>