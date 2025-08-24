<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Step 2</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="{{ asset('js/login.js') }}" defer></script>

</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="{{ asset('images/logo jbm.png') }}" alt="Logo PT JBM">
        </div>

        <h2 class="login-title">Verify OTP</h2>

        @if (isset($info))
            <div class="session-status">
                {{ $info }}
            </div>
        @endif

        @if ($errors->any())
            <div class="validation-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('otp.reset.verify') }}" class="login-form">
            @csrf

            <input type="hidden" name="username" value="{{ $username }}">

            <div class="form-group">
                <label for="otp">OTP</label>
                <input type="text" name="otp" id="otp" required placeholder="Enter the OTP">
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <div class="password-input-wrapper">
                    <input type="password"
                        name="password"
                        id="password"
                        required
                        minlength="6"
                        placeholder="Enter new password">
                    <button type="button" id="togglePassword" class="toggle-password" aria-label="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <div class="password-input-wrapper">
                    <input type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        required
                        minlength="6"
                        placeholder="Confirm new password">
                    <button type="button" id="togglePasswordConfirm" class="toggle-password" aria-label="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>


            <button type="submit" class="btn-login">Reset Password</button>
        </form>
    </div>
</body>
</html>
