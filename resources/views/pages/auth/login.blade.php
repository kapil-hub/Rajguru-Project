<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign in | Shaheed Rajguru College</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ '/images/logo/logo.png' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            color-scheme: light;
            --ink: #172d2a;
            --muted: #697c77;
            --green: #176b57;
            --green-dark: #104c40;
            --line: #dce7e2;
            --paper: #fbfcfa;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 28px;
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            background-color: #edf3ef;
            background-image: radial-gradient(#cbd9d1 0.7px, transparent 0.7px);
            background-size: 19px 19px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-shell {
            width: min(100%, 1040px);
            min-height: 650px;
            display: grid;
            grid-template-columns: 0.92fr 1.08fr;
            overflow: hidden;
            border: 1px solid rgba(23, 45, 42, 0.08);
            border-radius: 18px;
            background: var(--paper);
            box-shadow: 0 28px 80px rgba(27, 57, 47, 0.16);
        }

        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 42px;
            overflow: hidden;
            color: #f4faf5;
            background-color: var(--green-dark);
            background-image: linear-gradient(145deg, rgba(255,255,255,.045) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.045) 50%, rgba(255,255,255,.045) 75%, transparent 75%, transparent);
            background-size: 28px 28px;
        }

        .brand-panel::after {
            position: absolute;
            right: -125px;
            bottom: 92px;
            width: 310px;
            height: 310px;
            border: 1px solid rgba(220, 240, 226, 0.23);
            border-radius: 50%;
            box-shadow: 0 0 0 34px rgba(220, 240, 226, 0.045), 0 0 0 70px rgba(220, 240, 226, 0.035);
            content: '';
            pointer-events: none;
        }

        .brand-lockup,
        .brand-caption {
            position: relative;
            z-index: 1;
        }

        .brand-lockup {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .brand-lockup img {
            width: 52px;
            height: 52px;
            padding: 4px;
            border-radius: 12px;
            background: #fff;
            object-fit: contain;
        }

        .brand-lockup span {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1.5;
            text-transform: uppercase;
        }

        .brand-copy {
            max-width: 390px;
            margin: auto 0;
        }

        .brand-copy .eyebrow {
            margin: 0 0 16px;
            color: #b8d7c8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .brand-copy h1 {
            max-width: 360px;
            margin: 0;
            font-family: 'Manrope', sans-serif;
            font-size: clamp(32px, 4vw, 46px);
            font-weight: 700;
            line-height: 1.13;
            letter-spacing: 0;
        }

        .brand-copy p {
            max-width: 330px;
            margin: 18px 0 0;
            color: #d0e4d8;
            font-size: 14px;
            line-height: 1.75;
        }

        .brand-caption {
            margin: 0;
            color: #c5ddd0;
            font-size: 12px;
        }

        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 56px clamp(28px, 7vw, 76px);
        }

        .login-content {
            width: 100%;
            max-width: 390px;
        }

        .login-heading {
            margin-bottom: 30px;
        }

        .login-heading .kicker {
            margin: 0 0 9px;
            color: var(--green);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .login-heading h2 {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1.2;
        }

        .login-heading p {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .error {
            margin-bottom: 18px;
            padding: 11px 13px;
            border: 1px solid #f0c9c2;
            border-radius: 8px;
            color: #9d3528;
            background: #fff2ef;
            font-size: 13px;
        }

        .role-fieldset {
            min-width: 0;
            margin: 0 0 22px;
            padding: 0;
            border: 0;
        }

        .role-fieldset legend,
        .field-label {
            display: block;
            margin-bottom: 9px;
            color: #30433f;
            font-size: 13px;
            font-weight: 700;
        }

        .role-options {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .role-option input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            clip-path: inset(50%);
        }

        .role-option span {
            display: flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            padding: 9px 6px;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: #53655f;
            background: #fff;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: border-color 150ms ease, background-color 150ms ease, color 150ms ease;
        }

        .role-option input:checked + span {
            border-color: var(--green);
            color: var(--green-dark);
            background: #eaf4ee;
        }

        .role-option input:focus-visible + span {
            outline: 3px solid rgba(23, 107, 87, 0.25);
            outline-offset: 2px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .text-input {
            width: 100%;
            min-height: 48px;
            margin-top: 1px;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            outline: none;
            color: var(--ink);
            background: #fff;
            font: inherit;
            font-size: 14px;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .text-input::placeholder {
            color: #98a7a1;
        }

        .text-input:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(23, 107, 87, 0.12);
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap .text-input {
            padding-right: 72px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            padding: 5px;
            border: 0;
            color: var(--green);
            background: transparent;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            min-height: 49px;
            margin-top: 4px;
            border: 0;
            border-radius: 8px;
            color: #fff;
            background: var(--green);
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 150ms ease, transform 150ms ease;
        }

        .btn-login:hover {
            background: var(--green-dark);
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        .form-footer {
            display: flex;
            justify-content: center;
            margin-top: 17px;
            font-size: 13px;
        }

        .form-footer a {
            color: var(--green);
            font-weight: 700;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .copyright {
            margin: 32px 0 0;
            color: #84928d;
            font-size: 11px;
            text-align: center;
        }

        @media (max-width: 760px) {
            body {
                padding: 16px;
            }

            .login-shell {
                width: min(100%, 480px);
                min-height: auto;
                grid-template-columns: 1fr;
                border-radius: 14px;
            }

            .brand-panel {
                min-height: 210px;
                padding: 22px 25px;
            }

            .brand-copy {
                margin: 28px 0 0;
            }

            .brand-copy .eyebrow,
            .brand-copy p,
            .brand-caption {
                display: none;
            }

            .brand-copy h1 {
                max-width: none;
                font-size: 25px;
            }

            .brand-panel::after {
                right: -190px;
                bottom: -180px;
                width: 300px;
                height: 300px;
            }

            .form-panel {
                padding: 34px 25px 30px;
            }

            .login-heading {
                margin-bottom: 24px;
            }

            .login-heading h2 {
                font-size: 26px;
            }

            .copyright {
                margin-top: 25px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }

    </style>
</head>
<body>
<main class="login-shell">
    <section class="brand-panel" aria-label="College identity">
        <div class="brand-lockup">
            <img src="/images/logo/logo.png" alt="Shaheed Rajguru College logo">
            <span>अनुवर्तन<br>Shaheed Rajguru College</span>
        </div>
        <div class="brand-copy">
            <p class="eyebrow">Applied Sciences for Women</p>
            <h1>A place to learn, lead and make a difference.</h1>
            <p>Access your college portal for learning, teaching and administration.</p>
        </div>
        <p class="brand-caption">Open Learning Development Center</p>
    </section>

    <section class="form-panel" aria-labelledby="loginTitle">
        <div class="login-content">
            <header class="login-heading">
                <p class="kicker">College portal</p>
                <h2 id="loginTitle">Welcome back</h2>
                <p>Sign in to continue to your account.</p>
            </header>

            @if ($errors->any())
                <div class="error" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                <fieldset class="role-fieldset">
                    <legend>Choose your account</legend>
                    <div class="role-options">
                        <label class="role-option">
                            <input type="radio" name="role" value="admin" {{ old('role') == 'admin' ? 'checked' : '' }} required onchange="updatePlaceholder()">
                            <span>Admin</span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="student" {{ old('role') == 'student' ? 'checked' : '' }} required onchange="updatePlaceholder()">
                            <span>Student</span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="teacher" {{ old('role') == 'teacher' ? 'checked' : '' }} required onchange="updatePlaceholder()">
                            <span>Teacher</span>
                        </label>
                    </div>
                </fieldset>

                <div class="form-group">
                    <label class="field-label" id="loginLabel" for="loginInput">Login ID</label>
                    <input class="text-input" type="text" name="login" id="loginInput" value="{{ old('login') }}" placeholder="Choose your account type first" autocomplete="username" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="password-wrap">
                        <input class="text-input" type="password" name="password" id="password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" id="passwordToggle" onclick="togglePassword()" aria-label="Show password" aria-pressed="false">Show</button>
                    </div>
                </div>

                <button type="submit" class="btn-login">Sign in</button>
            </form>

            <div class="form-footer">
                <a href="/forgot-password">Forgot password?</a>
            </div>

            <p class="copyright">© {{ date('Y') }} Open Learning Development Center (OLDC)</p>
        </div>
    </section>
</main>

<script>
    function updatePlaceholder() {
        const selectedRole = document.querySelector('input[name="role"]:checked');
        const role = selectedRole ? selectedRole.value : '';
        const input = document.getElementById('loginInput');
        const label = document.getElementById('loginLabel');

        if (role === 'student') {
            input.placeholder = "Roll number / Email / Phone";
            label.innerText = "Student Login ID";
        } else if (role === 'teacher') {
            input.placeholder = "Employee ID / Email / Phone";
            label.innerText = "Teacher Login ID";
        } else if (role === 'admin') {
            input.placeholder = "Email address";
            label.innerText = "Admin Email";
        }else {
            input.placeholder = "Choose your account type first";
            label.innerText = "Login ID";
        }
    }

    function togglePassword() {
        const pwd = document.getElementById('password');
        const toggle = document.getElementById('passwordToggle');
        const isVisible = pwd.type === 'password';
        pwd.type = isVisible ? 'text' : 'password';
        toggle.textContent = isVisible ? 'Hide' : 'Show';
        toggle.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
        toggle.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
    }

    updatePlaceholder();
</script>

</body>
</html>
