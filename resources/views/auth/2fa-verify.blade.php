<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>التحقق من المصادقة الثنائية</title>
</head>
<body>
    <div class="container">
        <h3>أدخلي رمز المصادقة الثنائية</h3>
        <form method="POST" action="{{ route('2fa.verify') }}">
            @csrf
            <label>رمز التطبيق أو رمز احتياطي:</label>
            <input type="text" name="totp_code" required>
            <button type="submit">تحقق</button>
        </form>
    </div>
</body>
</html>