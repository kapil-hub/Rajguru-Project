<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
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

        .box {
            background: #fff;
            width: 380px;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(0,0,0,.25);
        }

        h2 {
            text-align: center;
            margin-bottom: 8px;
        }

        p {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 15px;
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

        .btn {
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

        .btn:hover {
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

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 8px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 12px;
            text-align: center;
        }

        .back {
            text-align: center;
            margin-top: 12px;
            font-size: 13px;
        }

        .back a {
            color: #4f46e5;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Forgot Password</h2>
    <p>We’ll send an OTP to your registered email</p>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="/forgot-password">
        @csrf

        <div class="form-group">
            <label>Login As</label>
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="admin">Admin</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter registered email" required>
        </div>

        <button class="btn">Send OTP</button>
    </form>

    <div class="back">
        <a href="/">← Back to Login</a>
    </div>
</div>

</body>
</html>
