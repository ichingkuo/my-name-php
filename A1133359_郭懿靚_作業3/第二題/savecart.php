<?php
session_start();
if (isset($_SESSION["ID"])) {
    $id       = $_SESSION["ID"];
    $name     = $_SESSION["Name"];
    $price    = $_SESSION["Price"];
    $quantity = $_SESSION["Quantity"];
    
    // 儲存為陣列 Cookie (有效期 1 小時)
    setcookie($id."[ID]",       $id,       time() + 3600);
    setcookie($id."[Name]",     $name,     time() + 3600);
    setcookie($id."[Price]",    $price,    time() + 3600);
    setcookie($id."[Quantity]", $quantity, time() + 3600);
}
header("Location: shoppingcart.php");
exit;
?>