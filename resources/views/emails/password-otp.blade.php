<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            background: #f3f4f6;
            font-family: 'Poppins', sans-serif;
        }
        .card {
            max-width: 420px;
            margin: 30px auto;
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0,0,0,.15);
            text-align: center;
        }
        .otp {
            font-size: 32px;
            letter-spacing: 6px;
            font-weight: bold;
            color: #4f46e5;
            margin: 20px 0;
        }
        .footer {
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Hello {{ $name }} 👋</h2>
    <p>You requested to reset your password.</p>

    <div class="otp">{{ $otp }}</div>

    <p>This OTP is valid for <strong>10 minutes</strong>.</p>

    <div class="footer">
        © {{ date('Y') }} Saheed Rajguru College
    </div>
</div>

</body>
</html>
