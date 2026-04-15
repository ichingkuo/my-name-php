<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>購物車內容</title></head>
<body>
<h2>購物車</h2>
<table border="1" cellpadding="5">
<tr><th>功能</th><th>編號</th><th>名稱</th><th>價格</th><th>數量</th></tr>
<?php
$total = 0;
$flag = true; // 用於交替列背景色

foreach ($_COOKIE as $arr => $value) {
    // 檢查是否為陣列 Cookie（即我們的商品資料）
    if (is_array($value)) {
        $color = $flag ? "#FF99CC" : "#99FFCC";
        $flag = !$flag;
        
        echo "<tr bgcolor='$color'>";
        // 刪除連結
        echo "<td><a href='delete.php?Id=$arr'>刪除</a></td>";
        
        $price = 0;
        $quantity = 0;
        // 顯示該商品各欄位
        foreach ($value as $key => $val) {
            echo "<td>$val</td>";
            if ($key == "Price")    $price = $val;
            if ($key == "Quantity") $quantity = $val;
        }
        $total += $price * $quantity;
        echo "</tr>";
    }
}
?>
<tr><td colspan="4" align="right">總金額 =</td><td>NT$<?php echo $total; ?>元</td></tr>
</table>
<p><a href="catalog.php">商品目錄</a> | <a href="shoppingcart.php">檢視購物車</a></p>
</body>
</html>