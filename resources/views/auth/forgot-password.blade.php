<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Logistics Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-container {
            display: flex;
            width: 90%;
            max-width: 1100px;
            height: 700px;
            background: #ffffff;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-image {
            flex: 1.3;
            background-color: #f4f5f7;
            background-image: url('{{ asset("assets/images/logistics_iso.png") }}');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
        }
        .login-form-container {
            flex: 1;
            padding: 80px 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #ffffff;
        }
        .login-header h1 {
            font-size: 32px;
            font-weight: 400;
            color: #1a1a1a;
            margin-bottom: 15px;
        }
        .login-header p {
            font-size: 14px;
            color: #9ca3af;
            line-height: 1.6;
            margin-bottom: 50px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 10px;
        }
        .form-group input[type="email"] {
            width: 100%;
            padding: 16px;
            background-color: #f3f4f6;
            border: 1px solid transparent;
            font-size: 14px;
            color: #374151;
            outline: none;
            transition: all 0.3s ease;
        }
        .form-group input[type="email"]:focus {
            border-color: #0f4c9c;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(15, 76, 156, 0.1);
        }
        .form-options {
            display: flex;
            align-items: center;
            margin-bottom: 35px;
        }
        .form-options a {
            font-size: 13px;
            color: #0f4c9c;
            text-decoration: none;
            font-weight: 500;
        }
        .form-options a:hover {
            text-decoration: underline;
        }
        .btn-submit {
            width: 100%;
            padding: 18px;
            background-color: #0f4c9c;
            color: #ffffff;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 20px;
        }
        .btn-submit:hover {
            background-color: #0b3977;
        }
        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
            font-size: 14px;
        }
        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #f87171;
        }
        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #86efac;
        }
        .alert ul {
            padding-left: 20px;
            margin: 0;
        }
        @media (max-width: 900px) {
            .login-container {
                flex-direction: column;
                height: auto;
                max-width: 500px;
                margin: 20px;
            }
            .login-image {
                height: 300px;
                flex: none;
            }
            .login-form-container {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-image"></div>
    <div class="login-form-container">
        <div class="login-header">
            <h1>Reset Password</h1>
            <p>Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="yatingzang0215@gmail.com">
            </div>

            <button type="submit" class="btn-submit">Email Password Reset Link</button>

            <div class="form-options">
                <a href="{{ route('login') }}">Back to Sign in</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
