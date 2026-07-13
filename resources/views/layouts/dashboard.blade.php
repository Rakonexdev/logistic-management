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

        [data-theme="light"] {
            --bg-color: #f8fafc;
            --surface-color: rgba(255, 255, 255, 0.7);
            --surface-hover: rgba(255, 255, 255, 0.9);
            --border-color: rgba(0, 0, 0, 0.08);
            --text-primary: #1e293b;
            --text-secondary: #64748b;
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

        .user-dropdown-wrapper {
            position: relative;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .user-info:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        [data-theme="light"] .user-info:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            min-width: 180px;
            padding: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.2s, transform 0.2s;
        }

        [data-theme="light"] .dropdown-menu {
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .dropdown-menu.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.75rem 1rem;
            background: transparent;
            border: none;
            color: var(--text-primary) !important;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.2s;
            text-align: left;
            text-decoration: none !important;
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        [data-theme="light"] .dropdown-item:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .dropdown-item.text-danger {
            color: var(--danger);
        }

        .dropdown-item.text-danger:hover {
            background: rgba(239, 68, 68, 0.1);
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

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }

        .btn-primary:hover {
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
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
                @if(Auth::user()->role === 'end_user')
                <!-- End User Navigation -->
                <div class="nav-group">
                    <div class="nav-group-title">Overview</div>
                    <a href="{{ url('end-user/dashboard') }}" class="nav-link {{ request()->is('*/dashboard') ? 'active' : '' }}">
                        <i class="ph ph-squares-four"></i> Dashboard
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
                    <a href="{{ route('asns.index') }}" class="nav-link {{ request()->is('*/asns*') ? 'active' : '' }}">
                        <i class="ph ph-download-simple"></i> Advance Shipping Note
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Outbound</div>
                    <a href="{{ route('sales-orders.index') }}" class="nav-link {{ request()->is('*/sales-orders*') ? 'active' : '' }}">
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

                <div class="nav-group">
                    <div class="nav-group-title">Reporting</div>
                    <a href="#" class="nav-link">
                        <i class="ph ph-chart-bar"></i> Reports
                    </a>
                </div>
                @else
                <!-- SFQ User Navigation -->
                <div class="nav-group">
                    <div class="nav-group-title">Overview</div>
                    <a href="{{ url('sfq-user/dashboard') }}" class="nav-link {{ request()->is('*/dashboard') ? 'active' : '' }}">
                        <i class="ph ph-squares-four"></i> Dashboard
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Inbound</div>
                    <a href="{{ route('sfq.grns.index') }}" class="nav-link {{ request()->is('*/grns*') ? 'active' : '' }}">
                        <i class="ph ph-download-simple"></i> GRN Confirmation
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Warehouse</div>
                    <a href="{{ route('sfq.locations.index') }}" class="nav-link {{ request()->is('*/locations*') ? 'active' : '' }}">
                        <i class="ph ph-stack"></i> Location & Stock
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Outbound</div>
                    <a href="{{ route('sfq.fulfillment.index') }}" class="nav-link {{ request()->is('*/fulfillment*') ? 'active' : '' }}">
                        <i class="ph ph-shopping-cart"></i> Order Fulfillment
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Logistics</div>
                    <a href="{{ route('sfq.deliveries.index') }}" class="nav-link {{ request()->is('*/deliveries*') ? 'active' : '' }}">
                        <i class="ph ph-truck"></i> Delivery Planning
                    </a>
                    <a href="{{ route('sfq.returns.index') }}" class="nav-link {{ request()->is('*/returns*') ? 'active' : '' }}">
                        <i class="ph ph-arrow-u-up-left"></i> Returns Execution
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Finance</div>
                    <a href="{{ route('sfq.cheques.index') }}" class="nav-link {{ request()->is('*/cheques*') ? 'active' : '' }}">
                        <i class="ph ph-bank"></i> Cheque Collections
                    </a>
                    <a href="{{ route('sfq.reconciliation.index') }}" class="nav-link {{ request()->is('*/reconciliation*') ? 'active' : '' }}">
                        <i class="ph ph-currency-circle-dollar"></i> Charges Recon
                    </a>
                    <a href="{{ route('sfq.invoices.index') }}" class="nav-link {{ request()->is('*/invoices*') ? 'active' : '' }}">
                        <i class="ph ph-receipt"></i> Invoicing
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Reporting</div>
                    <a href="{{ route('sfq.reports.index') }}" class="nav-link {{ request()->is('*/reports*') ? 'active' : '' }}">
                        <i class="ph ph-chart-bar"></i> Reports & Dashboards
                    </a>
                </div>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-wrapper">
            <nav class="navbar glass">
                <div style="flex: 1;"></div>
                <div class="user-nav">
                    <button class="icon-btn theme-toggle" id="theme-toggle" title="Toggle Theme" style="background: transparent; border: none; color: var(--text-primary); font-size: 1.25rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i class="ph ph-sun"></i>
                    </button>
                    @auth
                        <!-- Notification Bell Dropdown -->
                        <div class="user-dropdown-wrapper" style="position: relative; margin-right: 1rem;">
                            <div class="user-info" id="notificationDropdownTrigger" style="padding: 0.5rem; border-radius: 50%; width: 40px; height: 40px; justify-content: center; align-items: center; position: relative; cursor: pointer;">
                                <i class="ph ph-bell" style="font-size: 1.25rem; color: var(--text-primary);"></i>
                                <span id="notificationBadge" style="position: absolute; top: -2px; right: -2px; background: var(--danger); color: white; border-radius: 9999px; padding: 2px 6px; font-size: 0.65rem; font-weight: 700; line-height: 1; display: none;">0</span>
                            </div>
                            
                            <div class="dropdown-menu glass" id="notificationDropdownMenu" style="position: absolute; right: 0; top: 100%; width: 360px; max-height: 480px; overflow-y: auto; z-index: 1000; padding: 1rem; border-radius: 8px; margin-top: 0.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.3); display: none;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                                    <span style="font-weight: 700; font-size: 0.95rem;">Notifications</span>
                                    <button onclick="markAllNotificationsAsRead(event)" style="background: transparent; border: none; color: var(--accent-primary); font-size: 0.75rem; cursor: pointer; font-weight: 600;">Mark all as read</button>
                                </div>
                                <div id="notificationList" style="display: flex; flex-direction: column; gap: 0.5rem; max-height: 300px; overflow-y: auto;">
                                    <div style="color: var(--text-secondary); text-align: center; padding: 1.5rem; font-size: 0.85rem;">No new notifications</div>
                                </div>
                                <div style="border-top: 1px solid var(--border-color); padding-top: 0.75rem; margin-top: 0.75rem; text-align: center;">
                                    <a href="{{ route('notifications.index') }}" style="color: var(--accent-primary); text-decoration: none; font-size: 0.8rem; font-weight: 600;">View All Notifications</a>
                                </div>
                            </div>
                        </div>

                        <div class="user-dropdown-wrapper">
                            <div class="user-info" id="userDropdownTrigger">
                                <div class="user-avatar">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span>{{ Auth::user()->name }} ({{ Auth::user()->role }})</span>
                                <i class="ph ph-caret-down" style="font-size: 0.8rem; color: var(--text-secondary);"></i>
                            </div>
                            
                            <div class="dropdown-menu" id="userDropdownMenu">
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    <i class="ph ph-user"></i> My Profile
                                </a>
                                <a href="{{ route('profile.password') }}" class="dropdown-item">
                                    <i class="ph ph-key"></i> Reset Password
                                </a>
                                <div style="height: 1px; background: var(--border-color); margin: 0.5rem 0;"></div>
                                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="ph ph-sign-out"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </nav>

            <main class="container">
                @yield('content')
            </main>
        </div>
    </div>
    
    @stack('scripts')
    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const icon = themeToggle.querySelector('i');
        const currentTheme = localStorage.getItem('theme') || 'dark';

        if (currentTheme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
            icon.classList.remove('ph-sun');
            icon.classList.add('ph-moon');
        }

        themeToggle.addEventListener('click', () => {
            const isLight = document.documentElement.getAttribute('data-theme') === 'light';
            
            if (isLight) {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'dark');
                icon.classList.remove('ph-moon');
                icon.classList.add('ph-sun');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                icon.classList.remove('ph-sun');
                icon.classList.add('ph-moon');
            }
        });

        // User Dropdown Logic
        const userDropdownTrigger = document.getElementById('userDropdownTrigger');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        if (userDropdownTrigger) {
            userDropdownTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                if (notificationDropdownMenu) notificationDropdownMenu.classList.remove('show');
                userDropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!userDropdownTrigger.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                    userDropdownMenu.classList.remove('show');
                }
            });
        }

        // Notification Dropdown Logic
        const notificationDropdownTrigger = document.getElementById('notificationDropdownTrigger');
        const notificationDropdownMenu = document.getElementById('notificationDropdownMenu');

        if (notificationDropdownTrigger) {
            notificationDropdownTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                if (userDropdownMenu) userDropdownMenu.classList.remove('show');
                notificationDropdownMenu.classList.toggle('show');
                if (notificationDropdownMenu.classList.contains('show')) {
                    fetchNotificationsDropdown();
                }
            });

            document.addEventListener('click', (e) => {
                if (notificationDropdownTrigger && !notificationDropdownTrigger.contains(e.target) && !notificationDropdownMenu.contains(e.target)) {
                    notificationDropdownMenu.classList.remove('show');
                }
            });
        }

        function updateNotificationBadge() {
            fetch('{{ route('notifications.unread-count') }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    if (badge) {
                        if (data.unread_count > 0) {
                            badge.textContent = data.unread_count;
                            badge.style.display = 'block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(err => console.error('Error fetching unread count:', err));
        }

        function fetchNotificationsDropdown() {
            fetch('{{ route('notifications.index') }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('notificationList');
                    if (!list) return;
                    list.innerHTML = '';
                    
                    const notifications = data.data || [];
                    if (notifications.length === 0) {
                        list.innerHTML = '<div style="color: var(--text-secondary); text-align: center; padding: 1.5rem; font-size: 0.85rem;">No new notifications</div>';
                        return;
                    }

                    notifications.slice(0, 5).forEach(n => {
                        const item = document.createElement('div');
                        item.className = 'notification-item';
                        item.style.padding = '0.75rem';
                        item.style.borderRadius = '6px';
                        item.style.display = 'flex';
                        item.style.flexDirection = 'column';
                        item.style.gap = '0.25rem';
                        item.style.cursor = 'pointer';
                        item.style.borderBottom = '1px solid var(--border-color)';
                        item.style.background = n.is_read ? 'transparent' : 'rgba(99, 102, 241, 0.05)';
                        
                        let badgeColor = 'var(--text-secondary)';
                        let badgeBg = 'rgba(255, 255, 255, 0.05)';
                        if (n.type === 'success') { badgeColor = 'var(--success)'; badgeBg = 'rgba(16, 185, 129, 0.1)'; }
                        else if (n.type === 'warning') { badgeColor = 'var(--warning)'; badgeBg = 'rgba(245, 158, 11, 0.1)'; }
                        else if (n.type === 'error') { badgeColor = 'var(--danger)'; badgeBg = 'rgba(239, 68, 68, 0.1)'; }
                        else if (n.type === 'info') { badgeColor = 'var(--info)'; badgeBg = 'rgba(59, 130, 246, 0.1)'; }

                        const formattedDate = new Date(n.created_at).toLocaleString();

                        item.innerHTML = `
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                                <span style="font-weight: 600; font-size: 0.85rem; color: var(--text-primary);">${n.title}</span>
                                <span style="font-size: 0.65rem; padding: 1px 6px; border-radius: 4px; color: ${badgeColor}; background: ${badgeBg}; text-transform: uppercase; font-weight: 700;">${n.type}</span>
                            </div>
                            <p style="margin: 0; font-size: 0.8rem; color: var(--text-secondary); line-height: 1.4;">${n.message}</p>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.25rem; font-size: 0.7rem; color: var(--text-secondary);">
                                <span>${n.sender} ${n.doc_reference ? '• ' + n.doc_reference : ''}</span>
                                <span>${formattedDate}</span>
                            </div>
                        `;

                        item.addEventListener('click', () => {
                            handleNotificationClick(n.id, n.action_url);
                        });

                        list.appendChild(item);
                    });
                })
                .catch(err => console.error('Error fetching notifications:', err));
        }

        function handleNotificationClick(id, actionUrl) {
            fetch(`/api/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(() => {
                updateNotificationBadge();
                if (actionUrl) {
                    window.location.href = actionUrl;
                } else {
                    fetchNotificationsDropdown();
                }
            })
            .catch(err => console.error('Error marking notification as read:', err));
        }

        function markAllNotificationsAsRead(e) {
            if (e) e.stopPropagation();
            fetch('{{ route('notifications.read-all') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(() => {
                updateNotificationBadge();
                fetchNotificationsDropdown();
                if (window.location.pathname === '/notifications') {
                    window.location.reload();
                }
            })
            .catch(err => console.error('Error marking all notifications as read:', err));
        }

        // Initialize and poll notifications
        document.addEventListener('DOMContentLoaded', () => {
            updateNotificationBadge();
            setInterval(updateNotificationBadge, 30000);
        });
    </script>
</body>
</html>
