<html>
<head>
    <title>夏令營報名表 - 小小咖啡師培訓</title>
</head>
<body bgcolor="#fffaf3">

    <center>
        <font size="5" color="brown">
            <h1>2026最HOT的小小咖啡師培訓夏令營報名表</h1>
        </font>
        
        <form action="" method="POST">
            
            <!-- 基本資料表格 -->
            <table border="1" cellpadding="5">
                <!-- 第1列：姓名、血型 -->
                <tr>
                    <td width="80">姓名：</td>
                    <td width="200"><input type="text" name="name" placeholder="請輸入姓名"></td>
                    <td width="80">血型：</td>
                    <td width="200">
                        <select name="blood">
                            <option value="A型">A型</option>
                            <option value="B型">B型</option>
                            <option value="O型">O型</option>
                            <option value="AB型">AB型</option>
                            <option value="其他">其他</option>
                        </select>
                    </td>
                </tr>
                
                <!-- 第2列：生日、我是 -->
                <tr>
                    <td>生日：</td>
                    <td><input type="date" name="birth"></td>
                    <td>我是：</td>
                    <td nowrap>
                        <input type="radio" name="gender" value="帥哥"> 帥哥
                        <input type="radio" name="gender" value="美女"> 美女
                    </td>
                </tr>
                
                <!-- 第3列：身分、就讀學校 -->
                <tr>
                    <td>身分：</td>
                    <td>
                        <select name="identity">
                            <option value="國小">國小</option>
                            <option value="國中">國中</option>
                            <option value="高中">高中</option>
                            <option value="大學">大學</option>
                            <option value="其他">其他</option>
                        </select>
                    </td>
                    <td>就讀學校：</td>
                    <td><input type="text" name="school" placeholder="範例：快樂國小"></td>
                </tr>
                
                <!-- 第4列：班級、身分證字號 -->
                <tr>
                    <td>班級：</td>
                    <td><input type="text" name="class" placeholder="例如：五年二班"></td>
                    <td>身分證字號：</td>
                    <td><input type="text" name="id" placeholder="範例：A123456789"></td>
                </tr>
            </table>      
            <br>
            
            <!-- 緊急聯絡人 -->
            <h3>緊急聯絡人</h3>
            <table border="1" cellpadding="5">
                <tr>
                    <td width="33%">稱謂：<input type="text" name="contact_title" placeholder="例如：父親"></td>
                    <td width="33%">姓名：<input type="text" name="contact_name" placeholder="例如：王大明"></td>
                    <td width="34%">手機：<input type="text" name="contact_phone" placeholder="0912-345-678"></td>
                </tr>
            </table>
            
            <br>
            
            <!-- 對咖啡的了解度 -->
            <h3>對咖啡的了解度</h3>
            <table border="1" cellpadding="8" >
                <tr>
                    <td align="center">
                        <font size="4">完全不了解</font>
                        &nbsp;&nbsp;&nbsp;
                        <input type="range" name="coffee_knowledge" min="0" max="100" value="50">
                        &nbsp;&nbsp;&nbsp;
                        <font size="4">完全了解</font>
                        <br>
                        <font size="2">(向左滑動表示了解較少，向右滑動表示了解較多)</font>
                    </td>
                </tr>
            </table>
            
            <br>
            
            <!-- 報名選項 -->
            <h3>報名選項</h3>
            <table border="1" cellpadding="5">
                <tr>
                    <td nowrap>
                        <input type="radio" name="register_type" value="新生"> 新生 &nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="register_type" value="舊生"> 舊生 &nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="register_type" value="早鳥者"> 早鳥者
                    </td>
                </tr>
                <tr>
                    <td>
                        想學習的技巧：
                        <input type="checkbox" name="skills" value="手沖咖啡"> 手沖咖啡 &nbsp;&nbsp;
                        <input type="checkbox" name="skills" value="拉花"> 拉花 &nbsp;&nbsp;
                        <input type="checkbox" name="skills" value="咖啡豆分辨"> 咖啡豆分辨 &nbsp;&nbsp;
                        <input type="checkbox" name="skills" value="咖啡產地介紹"> 咖啡產地介紹 &nbsp;&nbsp;
                        <input type="checkbox" name="skills" value="其他"> 其他
                    </td>
                </tr>
            </table>
            
            <p align="center">
                <font color="red" size="4">
                    ~5/30 前報名享有早鳥優惠95折
                </font>
            </p>
            
            <hr>
            
            <!-- 家長同意書 -->
            <h2>2026最HOT的小小咖啡師培訓夏令營家長同意書</h2>
            <p>本人同意子女參加2026最HOT的小小咖啡師培訓夏令營，願意遵守相關生活安全之規定，並主動告知子女特殊身心狀況、食(藥)物過敏等須注意事項，以做良好處理。</p>
            
            <table border="1" cellpadding="5">
                <tr>
                    <td width="33%">同意人：<input type="text" name="parent_name" placeholder="家長/監護人姓名"></td>
                    <td width="33%">關係：<input type="text" name="relation" placeholder="例如：母女"></td>
                    <td width="34%">填表日期：<input type="date" name="date"></td>
                </tr>
            </table>
            
            <br>
            <div align="center">
                <input type="submit" value="送出報名表">
                <input type="reset" value="重新填寫">
            </div>
        </form>
    </center>
    
</body>
</html>