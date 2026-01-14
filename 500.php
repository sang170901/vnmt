<?php
/**
 * 500 Error Page
 */
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Lỗi Server | VNMaterial</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            text-align: center;
            background: white;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
        }
        
        h1 {
            font-size: 120px;
            color: #f5576c;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 15px;
        }
        
        p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(245, 87, 108, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>500</h1>
        <h2>Lỗi Server</h2>
        <p>Xin lỗi, đã có lỗi xảy ra trên server. Chúng tôi đang khắc phục vấn đề này.</p>
        <a href="/" class="btn">← Về Trang Chủ</a>
    </div>
</body>
</html>

