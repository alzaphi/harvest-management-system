<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="{{ asset('images/logo jbm.png') }}" alt="Logo PT JBM">
        </div>

        <h2 class="login-title">Reset Password</h2>

        <div class="instruction">
            <p>Forgot your password? No problem. Just enter your email address and we’ll send you a link to reset it.</p>
        </div>

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

        <form method="POST" action="{{ route('password.email') }}" class="login-form">
            @csrf

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text"
                 name="username"
                 id="username"
                 value="{{ old('username') }}"
                 required
                 autofocus
                 placeholder="Enter your username...">
            </div>

            <button type="submit" class="btn-login">Send Reset Link</button>

            <div class="forgot-password">
                <a href="{{ route('login') }}">Back to Login</a>
            </div>
        </form>

    </div>
</body>
</html>
