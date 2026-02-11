<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
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
            width: 360px;
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
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        label {
            font-size: 13px;
            color: #444;
        }

        input {
            width: 100%;
            padding: 11px;
            border-radius: 7px;
            border: 1px solid #ddd;
            margin-top: 6px;
        }

        button {
            width: 100%;
            margin-top: 12px;
            padding: 12px;
            background: #4f46e5;
            border: none;
            color: white;
            font-size: 15px;
            border-radius: 7px;
            cursor: pointer;
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

<div class="box">
    <h2>Create New Password</h2>
    <p>Choose a strong password</p>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/reset-password">
        @csrf

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <button>Reset Password</button>
    </form>
</div>

</body>
</html>
