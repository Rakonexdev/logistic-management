<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Logistics Platform</title>
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
            padding: 60px 80px;
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
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 8px;
        }
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 14px;
            background-color: #f3f4f6;
            border: 1px solid transparent;
            font-size: 14px;
            color: #374151;
            outline: none;
            transition: all 0.3s ease;
        }
        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus {
            border-color: #0f4c9c;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(15, 76, 156, 0.1);
        }
        .btn-submit {
            width: 100%;
            padding: 16px;
            background-color: #0f4c9c;
            color: #ffffff;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 15px;
        }
        .btn-submit:hover {
            background-color: #0b3977;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }
        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #f87171;
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
            <p>Please enter your email and set your new password below.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus placeholder="yatingzang0215@gmail.com">
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••••••">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••••••">
            </div>

            <button type="submit" class="btn-submit">Reset Password</button>
        </form>
    </div>
</div>

</body>
</html>
