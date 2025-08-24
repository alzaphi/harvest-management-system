<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="{{ asset('js/login.js') }}" defer></script>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="{{ asset('images/logo jbm.png') }}" alt="Logo PT JBM">
        </div>

        <h2 class="login-title">Login</h2>

        <!-- Session Status -->
        @if (session('status'))
            <div class="session-status">
                {{ session('status') }}
            </div>
        @endif

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="validation-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf

            <div class="form-group">
                <label for="username">Username (Email / Phone)</label>
                <input type="text"
                       name="username"
                       id="username"
                       value="{{ old('username') }}"
                       required
                       autofocus
                       placeholder="Enter your username (email or phone)">
            </div>

            <div class="form-group password-container">
                <label for="password">Password</label>
                <div class="password-input-wrapper">
                    <input type="password"
                           name="password"
                           id="password"
                           required
                           minlength="6"
                           autocomplete="current-password"
                           placeholder="Enter your password...">
                    <button type="button" id="togglePassword" class="toggle-password" aria-label="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox"
                       name="remember"
                       id="remember"
                       {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn-login">Login</button>

            <div class="forgot-password">
                <a href="{{ route('otp.reset.request') }}">Forgot your password?</a>
            </div>
        </form>
    </div>
</body>
</html>
