<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
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
            text-align: center;
        }

        h2 {
            margin-bottom: 8px;
        }

        p {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            font-size: 18px;
            text-align: center;
            letter-spacing: 6px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        input:focus {
            outline: none;
            border-color: #6366f1;
        }

        button {
            width: 100%;
            margin-top: 15px;
            padding: 12px;
            background: #4f46e5;
            border: none;
            color: white;
            font-size: 15px;
            border-radius: 8px;
            cursor: pointer;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 8px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 8px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>OTP Verification</h2>
    <p>Enter the 6-digit OTP sent to your email</p>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="/verify-otp">
        @csrf
        <input type="text" name="otp" maxlength="6" placeholder="••••••" required>
        <button>Verify OTP</button>
    </form>
</div>

</body>
</html>
