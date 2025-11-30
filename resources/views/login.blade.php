<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    @if (session('success'))
    <script>
        alert("{{ session('success') }}");
    </script>
    @endif
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            direction: rtl;
            padding: 40px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"] {
            width: 250px;
            padding: 6px;
            font-size: 14px;
        }

        .toggle-password {
            cursor: pointer;
            margin-right: 8px;
            font-size: 13px;
            color: #0077cc;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        /* تنسيق النافذة */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #ccc;
            width: 350px;
            text-align: right;
        }

        .modal-content h3 {
            margin-top: 0;
        }

        .modal-content input {
            width: 100%;
            padding: 6px;
            margin-bottom: 10px;
        }

        .modal-content button {
            padding: 6px 12px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <h2>تسجيل الدخول</h2>

    @if ($errors->any())
        <div class="error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="national_id">الرقم الوطني:</label><br>
        <input type="text" name="national_id" id="national_id"><br><br>

        <label for="password">كلمة المرور:</label><br>
        <input type="password" name="password" id="password">
        <span class="toggle-password" onclick="togglePassword()">👁️ إظهار</span><br><br>

        <button type="submit">دخول</button>
    </form>

    <!-- نافذة تغيير كلمة السر -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content">
            <h3>تغيير كلمة السر</h3>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="national_id" value="{{ old('national_id') }}">

                <label>كلمة السر الحالية:</label>
                <input type="password" name="current_password" required>

                <label>كلمة السر الجديدة:</label>
                <input type="password" name="new_password" required>

                <label>تأكيد كلمة السر الجديدة:</label>
                <input type="password" name="new_password_confirmation" required>

                <button type="submit">تغيير</button>
                <button type="button" onclick="closeModal()">إلغاء</button>
            </form>
        </div>
    </div>

    

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const toggleText = document.querySelector(".toggle-password");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleText.textContent = "👁️ إخفاء";
            } else {
                passwordInput.type = "password";
                toggleText.textContent = "👁️ إظهار";
            }
        }

        function openModal() {
            document.getElementById('changePasswordModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('changePasswordModal').style.display = 'none';
        }

        function openTotpModal() {
            document.getElementById('totpModal').style.display = 'block';
        }

        function closeTotpModal() {
            document.getElementById('totpModal').style.display = 'none';
        }

        // ⭐ إذا الخطأ كان بسبب انتهاء صلاحية كلمة السر، افتح النافذة مباشرة
        @if($errors->has('password') || $errors->has('current_password'))
            openModal();
        @endif

        // ⭐ إذا رجعنا من الـ Controller مع فلاغ show_totp_modal، افتح نافذة TOTP مباشرة
        @if(session('show_totp_modal'))
            openTotpModal();
        @endif
    </script>
</body>
</html>