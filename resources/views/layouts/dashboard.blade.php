<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Logistic Management</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --bg-color: #0f111a;
            --surface-color: rgba(30, 33, 43, 0.7);
            --surface-hover: rgba(45, 48, 60, 0.9);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f8f9fa;
            --text-secondary: #adb5bd;
            --accent-primary: #6366f1;
            --accent-secondary: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .glass {
            background: var(--surface-color);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
        }

        .layout-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0;
            border-right: 1px solid var(--border-color);
            border-radius: 0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .brand {
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(to right, var(--accent-primary), var(--accent-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
            flex: 1;
        }

        .nav-group {
            margin-bottom: 1.5rem;
        }

        .nav-group-title {
            padding: 0 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: var(--text-primary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            border-left-color: var(--text-secondary);
        }

        .nav-link.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--accent-primary);
            border-left-color: var(--accent-primary);
        }

        .nav-link i {
            font-size: 1.25rem;
        }

        /* Main Content Styling */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .navbar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            border-radius: 0;
            border-left: none;
            border-right: none;
            border-top: none;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        
        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem 2rem 2rem;
            width: 100%;
            box-sizing: border-box;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-color);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 1024px) {
            .sidebar {
                position: absolute;
                transform: translateX(-100%);
                z-index: 1000;
                transition: transform 0.3s;
            }
            .sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="layout-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar glass">
            <div class="sidebar-header">
                <div class="brand">
                    <i class="ph ph-package" style="color: var(--accent-primary)"></i>
                    LogisticsPro
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-group">
                    <div class="nav-group-title">Overview</div>
                    <a href="{{ request()->is('end-user*') ? url('end-user/dashboard') : url('sfq-user/dashboard') }}" class="nav-link {{ request()->is('*/dashboard') ? 'active' : '' }}">
                        <i class="ph ph-squares-four"></i> Dashboard
                    </a>
                    <a href="#" class="nav-link">
                        <i class="ph ph-chart-bar"></i> Reports
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Inventory</div>
                    <a href="{{ route('products.index') }}" class="nav-link {{ request()->is('*/products*') ? 'active' : '' }}">
                        <i class="ph ph-box-box"></i> SKU Management
                    </a>
                    <a href="#" class="nav-link">
                        <i class="ph ph-stack"></i> Stock Visibility
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Inbound</div>
                    <a href="#" class="nav-link">
                        <i class="ph ph-download-simple"></i> Advance Shipping Note
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Outbound</div>
                    <a href="#" class="nav-link">
                        <i class="ph ph-shopping-cart"></i> Sales Orders
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Reverse Logistics</div>
                    <a href="#" class="nav-link">
                        <i class="ph ph-arrow-u-up-left"></i> Return Instructions
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Finance</div>
                    <a href="#" class="nav-link">
                        <i class="ph ph-receipt"></i> Invoices
                    </a>
                    <a href="#" class="nav-link">
                        <i class="ph ph-currency-circle-dollar"></i> Charge Proposals
                    </a>
                    <a href="#" class="nav-link">
                        <i class="ph ph-bank"></i> Cheque Collections
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-wrapper">
            <nav class="navbar glass">
                <div class="user-nav">
                    @auth
                        <div class="user-info">
                            <div class="user-avatar">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span>{{ Auth::user()->name }} ({{ Auth::user()->role }})</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="ph ph-sign-out"></i> Logout
                            </button>
                        </form>
                    @endauth
                </div>
            </nav>

            <main class="container">
                @yield('content')
            </main>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>
