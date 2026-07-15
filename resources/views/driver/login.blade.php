<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Sign in - Mobile Logistics App</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --bg-color: #0b0c10;
            --surface-color: rgba(22, 26, 35, 0.65);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f8f9fa;
            --text-secondary: #8e9aab;
            --accent-primary: #8b5cf6;
            --accent-glow: rgba(139, 92, 246, 0.4);
            --accent-secondary: #6366f1;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        /* Simulator Frame for desktop, clean full-screen for mobile */
        .mobile-frame {
            width: 100%;
            max-width: 412px;
            height: 100%;
            min-height: 780px;
            background: var(--surface-color);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 40px rgba(139, 92, 246, 0.15);
            display: flex;
            flex-direction: column;
            padding: 2.5rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .mobile-frame::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 25px;
            background: #000000;
            border-bottom-left-radius: 18px;
            border-bottom-right-radius: 18px;
            z-index: 10;
        }

        @media (max-width: 480px) {
            body {
                padding: 0;
                background: var(--bg-color);
            }
            .mobile-frame {
                max-width: 100%;
                min-height: 100vh;
                border-radius: 0;
                border: none;
                padding: 3rem 1.5rem 1.5rem 1.5rem;
            }
            .mobile-frame::before {
                display: none;
            }
        }

        .header {
            text-align: center;
            margin-top: 3.5rem;
            margin-bottom: 3.5rem;
        }

        .logo-icon {
            font-size: 3.5rem;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 10px rgba(139, 92, 246, 0.3));
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            margin-bottom: 0.5rem;
        }

        .header p {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .form-group {
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            position: relative;
        }

        .form-group label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            color: var(--text-secondary);
            font-size: 1.25rem;
            transition: color 0.3s;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s;
        }

        .form-input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent-primary);
            box-shadow: 0 0 15px var(--accent-glow);
        }

        .form-input:focus + i {
            color: var(--accent-primary);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            margin-bottom: 2rem;
            color: var(--text-secondary);
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .remember-me input {
            accent-color: var(--accent-primary);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px var(--accent-glow);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }

        .alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 0.85rem;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }

        .alert ul {
            padding-left: 1.25rem;
            margin: 0;
        }

        .footer-link {
            text-align: center;
            margin-top: auto;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .footer-link a {
            color: var(--accent-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="mobile-frame">
    <div class="header">
        <i class="ph ph-truck logo-icon"></i>
        <h1>Driver Connect</h1>
        <p>Logistics Mobile Execution Hub</p>
    </div>

    @if($errors->any())
        <div class="alert">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('driver.login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Driver Email</label>
            <div class="input-wrapper">
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="form-input" placeholder="driver@gmail.com">
                <i class="ph ph-envelope"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
                <input type="password" id="password" name="password" required class="form-input" placeholder="••••••••">
                <i class="ph ph-lock"></i>
            </div>
        </div>

        <div class="form-options">
            <label class="remember-me">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                Remember Trip
            </label>
        </div>

        <button type="submit" class="btn-submit">
            <i class="ph ph-sign-in"></i> Sign In to Shift
        </button>
    </form>

    <div class="footer-link">
        Are you an operator? <a href="{{ route('login') }}">Go to Web portal</a>
    </div>
</div>

</body>
</html>
