<!DOCTYPE html>
<html>
<body style="margin:0; padding:0; background:#f3f4f6; font-family:Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6; padding:30px 0;">
    <tr>
        <td align="center">

            <table width="420" cellpadding="0" cellspacing="0"
                   style="background:#ffffff; border-radius:14px; box-shadow:0 10px 25px rgba(0,0,0,.15); padding:25px; text-align:center;">

                <!-- Logo -->
                <tr>
                    <td style="padding-bottom:15px;">
                        <img src="{{ asset('images/logo/logo.png') }}"
                             alt="Rajguru College"
                             width="80"
                             style="display:block; margin:0 auto;">
                    </td>
                </tr>

                <!-- Heading -->
                <tr>
                    <td>
                        <h2 style="margin:10px 0; color:#111827;">
                            Hello {{ $name }} 👋
                        </h2>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="color:#374151; font-size:14px;">
                        <p style="margin:10px 0;">
                            You requested to reset your password.
                        </p>
                    </td>
                </tr>

                <!-- OTP -->
                <tr>
                    <td>
                        <div style="
                            font-size:32px;
                            letter-spacing:6px;
                            font-weight:bold;
                            color:#4f46e5;
                            margin:20px 0;">
                            {{ $otp }} 
                        </div>
                    </td>
                </tr>

                <!-- Validity -->
                <tr>
                    <td style="font-size:13px; color:#374151;">
                        This OTP is valid for <strong>10 minutes</strong>.
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="font-size:12px; color:#6b7280; padding-top:20px;">
                        © {{ date('Y') }} Shaheed Rajguru College<br>
                        Delhi University
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
