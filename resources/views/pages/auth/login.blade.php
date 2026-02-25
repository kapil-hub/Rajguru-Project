<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>College Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            background: linear-gradient(135deg, #4f46e5, #9333ea);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background: #fff;
            width: 380px;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(0,0,0,.25);
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
            color:#4f46e5;
            font-size: 17px;
        }

        .anv{
            text-align: center;
            background-image: linear-gradient(to bottom, red, orange, #3e3e34, green, blue, indigo, violet);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            font-size: 24px;
            font-weight:900
        }
        p {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        label {
            font-size: 13px;
            color: #444;
        }

        select, input {
            width: 100%;
            padding: 11px 12px;
            border-radius: 7px;
            border: 1px solid #ddd;
            margin-top: 6px;
            font-size: 14px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #6366f1;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 38px;
            cursor: pointer;
            font-size: 13px;
            color: #666;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #4f46e5;
            border: none;
            color: white;
            font-size: 15px;
            border-radius: 7px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #4338ca;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 8px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 12px;
            text-align: center;
        }

        .footer-text {
            text-align: center;
            margin-top: 15px;
            font-size: 12px;
            color: #777;
        }
        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 8px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 12px;
            text-align: center;
        }

    </style>
</head>
<body>

<div class="login-box">
    <center><img src="/images/logo/logo.png" width="150"></center>
    <center><xrt class="anv">अनुवर्तन</xrt></center>
    <h2>Shaheed Rajguru College of Applied Sciences for Women</h2>
    {{-- <p>Student / Teacher Portal</p> --}}
    
    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="form-group">
            <label>Login As</label>
            <select name="role" id="role" required onchange="updatePlaceholder()">
                <option value="">-- Select Role --</option>
                <option value="admin" {{ old('role')=='admin' ? 'selected' : '' }}>Admin</option>
                <option value="student" {{ old('role')=='student' ? 'selected' : '' }}>Student</option>
                <option value="teacher" {{ old('role')=='teacher' ? 'selected' : '' }}>Teacher</option>
            </select>

        </div>

        <div class="form-group">
            <label id="loginLabel">Login ID</label>
            <input type="text" name="login" id="loginInput" placeholder="Roll Number / Email / Phone" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" id="password">
            <span class="password-toggle" onclick="togglePassword()">👁</span>
        </div>

        <button type="submit" class="btn-login">Login</button>

    </form>
        <div class="footer-text">
            <a href="/forgot-password" style="color:#4f46e5;text-decoration:none;">
                Forgot Password?
            </a>
        </div>

    <div class="footer-text">
        © {{ date('Y') }} Open Learning Development Center (OLDC)
    </div>
</div>

<script>
    function updatePlaceholder() {
        const role = document.getElementById('role').value;
        const input = document.getElementById('loginInput');
        const label = document.getElementById('loginLabel');

        if (role === 'student') {
            input.placeholder = "Roll No / Email / Phone";
            label.innerText = "Student Login ID";
        } else if (role === 'teacher') {
            input.placeholder = "Employee ID / Email / Phone";
            label.innerText = "Teacher Login ID";
        } else if (role === 'admin') {
            input.placeholder = "Gmail";
            label.innerText = "Admin Email";
        }else {
            input.placeholder = "Select role first";
            label.innerText = "Login ID";
        }
    }

    function togglePassword() {
        const pwd = document.getElementById('password');
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
    }
</script>

</body>
</html>
