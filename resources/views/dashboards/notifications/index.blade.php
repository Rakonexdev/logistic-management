@extends('layouts.dashboard')

@push('styles')
    <style>
        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .actions-group {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        /* Filter Panel */
        .filter-panel {
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 12px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: flex-end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-input, .form-select {
            width: 100%;
            box-sizing: border-box;
            padding: 0.65rem 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        [data-theme="light"] .form-input, 
        [data-theme="light"] .form-select {
            background: rgba(0, 0, 0, 0.02);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-select option {
            background: var(--bg-color);
            color: var(--text-primary);
        }

        /* Notifications List */
        .notification-card {
            padding: 1.25rem;
            margin-bottom: 1rem;
            border-radius: 12px;
            display: flex;
            gap: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .notification-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .notification-card.unread {
            background: rgba(99, 102, 241, 0.06);
            border-left: 4px solid var(--accent-primary);
        }

        .icon-container {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .type-success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .type-warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .type-error { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .type-info { background: rgba(59, 130, 246, 0.1); color: var(--info); }

        .notification-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .notification-title-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .notification-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-primary);
            margin: 0;
        }

        .notification-message {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .notification-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        .notification-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            align-self: center;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-secondary);
            transition: all 0.2s;
        }

        .btn-icon:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }

        .btn-icon.delete:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        /* Pagination style */
        .pagination-container {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 0.5rem;
        }

        .pagination li a, .pagination li span {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .pagination li a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .pagination li.active span {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
        }

        .pagination li.disabled span {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-bell"></i> Notification Center
        </h1>
        <div class="actions-group">
            <form action="{{ route('notifications.trigger-demo') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-outline">
                    <i class="ph ph-lightning"></i> Trigger Demo Alerts
                </button>
            </form>
            <button onclick="markAllNotificationsAsRead()" class="btn btn-primary">
                <i class="ph ph-check-square"></i> Mark All as Read
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.875rem;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters Panel -->
    <div class="filter-panel glass">
        <form action="{{ route('notifications.index') }}" method="GET">
            <div class="filter-grid">
                <div class="form-group">
                    <label class="form-label" for="search">Doc Reference</label>
                    <input type="text" id="search" name="search" class="form-input" placeholder="e.g. SO-2026-0001" value="{{ request('search') }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="module">Module</label>
                    <select id="module" name="module" class="form-select">
                        <option value="">All Modules</option>
                        @foreach ($modules as $mod)
                            <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $mod)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="type">Notification Type</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Info</option>
                        <option value="success" {{ request('type') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="error" {{ request('type') == 'error' ? 'selected' : '' }}>Error</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="read_status">Read Status</label>
                    <select id="read_status" name="read_status" class="form-select">
                        <option value="">All</option>
                        <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>Unread</option>
                        <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>Read</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-input" value="{{ request('start_date') }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-input" value="{{ request('end_date') }}">
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.65rem;">
                        <i class="ph ph-funnel"></i> Filter
                    </button>
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline" style="padding: 0.65rem;" title="Reset Filters">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Notifications List -->
    <div class="notifications-list">
        @forelse ($notifications as $n)
            <div class="notification-card glass {{ $n->is_read ? '' : 'unread' }}" id="notification-card-{{ $n->id }}" style="cursor: pointer;" onclick="handleNotificationCardClick(event, {{ $n->id }}, '{{ $n->action_url }}')">
                <div class="icon-container type-{{ $n->type }}">
                    @if ($n->type === 'success')
                        <i class="ph ph-check-circle"></i>
                    @elseif ($n->type === 'warning')
                        <i class="ph ph-warning"></i>
                    @elseif ($n->type === 'error')
                        <i class="ph ph-x-circle"></i>
                    @else
                        <i class="ph ph-info"></i>
                    @endif
                </div>

                <div class="notification-body">
                    <div class="notification-title-bar">
                        <h2 class="notification-title">{{ $n->title }}</h2>
                        <span style="font-size: 0.75rem; color: var(--text-secondary);">
                            {{ $n->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <p class="notification-message">{{ $n->message }}</p>
                    <div class="notification-meta">
                        <span><strong>Module:</strong> {{ ucfirst(str_replace('_', ' ', $n->module)) }}</span>
                        @if ($n->doc_reference)
                            <span><strong>Doc Reference:</strong> {{ $n->doc_reference }}</span>
                        @endif
                        <span><strong>Sender:</strong> {{ $n->sender }}</span>
                        <span><strong>Status:</strong> {{ ucfirst($n->status) }}</span>
                    </div>
                </div>

                <div class="notification-actions">
                    @if ($n->action_url)
                        <a href="#" onclick="handleNotificationClick({{ $n->id }}, '{{ $n->action_url }}'); return false;" class="btn-icon" title="View Related Document">
                            <i class="ph ph-arrow-square-out"></i>
                        </a>
                    @endif
                    @if (!$n->is_read)
                        <button onclick="markSingleAsRead({{ $n->id }})" class="btn-icon" title="Mark as Read" id="btn-read-{{ $n->id }}">
                            <i class="ph ph-check"></i>
                        </button>
                    @endif
                    <button onclick="deleteNotification({{ $n->id }})" class="btn-icon delete" title="Delete Notification">
                        <i class="ph ph-trash"></i>
                    </button>
                </div>
            </div>
        @empty
            <div class="glass" style="text-align: center; padding: 4rem; border-radius: 12px; color: var(--text-secondary);">
                <i class="ph ph-bell-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; display: block;"></i>
                <p style="margin: 0; font-size: 1.1rem; font-weight: 500;">No notifications found</p>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">Try clearing your filters or click "Trigger Demo Alerts" to populate some data.</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="pagination-container">
            {{ $notifications->links() }}
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        function handleNotificationCardClick(event, id, actionUrl) {
            // Ignore click if it was on the delete button or the mark-as-read button
            if (event.target.closest('.btn-icon.delete') || event.target.closest('.btn-icon[id^="btn-read-"]')) {
                return;
            }

            fetch(`/api/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge();
                    if (actionUrl && actionUrl !== '') {
                        window.location.href = actionUrl;
                    } else {
                        const card = document.getElementById(`notification-card-${id}`);
                        if (card) {
                            card.classList.remove('unread');
                        }
                        const btn = document.getElementById(`btn-read-${id}`);
                        if (btn) {
                            btn.remove();
                        }
                    }
                }
            })
            .catch(err => console.error('Error handling notification click:', err));
        }

        function markSingleAsRead(id) {
            fetch(`/api/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById(`notification-card-${id}`);
                    if (card) {
                        card.classList.remove('unread');
                    }
                    const btn = document.getElementById(`btn-read-${id}`);
                    if (btn) {
                        btn.remove();
                    }
                    updateNotificationBadge();
                }
            })
            .catch(err => console.error('Error marking read:', err));
        }

        function deleteNotification(id) {
            if (!confirm('Are you sure you want to delete this notification?')) return;
            fetch(`/api/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById(`notification-card-${id}`);
                    if (card) {
                        card.remove();
                    }
                    updateNotificationBadge();
                    // If list is empty, reload
                    if (document.querySelectorAll('.notification-card').length === 0) {
                        window.location.reload();
                    }
                }
            })
            .catch(err => console.error('Error deleting:', err));
        }
    </script>
@endpush
