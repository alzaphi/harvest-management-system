<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Step 1</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="{{ asset('images/logo jbm.png') }}" alt="Logo PT JBM">
        </div>

        <h2 class="login-title">Reset Password (Step 1)</h2>

        @if ($errors->any())
            <div class="validation-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('otp.reset.send') }}" class="login-form">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text"
                       name="username"
                       id="username"
                       required
                       value="{{ old('username') }}"
                       placeholder="Enter your username (HP/email)">
            </div>
            <button type="submit" class="btn-login">Send OTP</button>
        </form>
    </div>
</body>
</html>
