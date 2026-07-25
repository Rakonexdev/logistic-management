<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Hub - Logistics Mobile</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --bg-color: #0b0c10;
            --surface-color: rgba(22, 26, 35, 0.7);
            --surface-hover: rgba(33, 38, 50, 0.9);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f8f9fa;
            --text-secondary: #8e9aab;
            --accent-primary: #8b5cf6;
            --accent-glow: rgba(139, 92, 246, 0.3);
            --accent-secondary: #6366f1;

            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
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
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.12) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        /* Simulator Frame for desktop, full screen for mobile */
        .mobile-frame {
            width: 100%;
            max-width: 412px;
            height: 820px;
            background: var(--surface-color);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--border-color);
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 40px rgba(139, 92, 246, 0.1);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        /* Screen top notch */
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
            z-index: 100;
        }

        @media (max-width: 480px) {
            body {
                padding: 0;
            }

            .mobile-frame {
                max-width: 100%;
                height: 100vh;
                border-radius: 0;
                border: none;
            }

            .mobile-frame::before {
                display: none;
            }
        }

        /* App Header */
        .app-header {
            padding: 2.5rem 1.25rem 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 0, 0, 0.15);
            flex-shrink: 0;
        }

        .driver-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.3);
        }

        .driver-details h2 {
            font-size: 0.95rem;
            font-weight: 600;
        }

        .driver-details p {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.2s;
            display: flex;
            align-items: center;
        }

        .logout-btn:hover {
            color: var(--danger);
        }

        /* Screen Content Area */
        .app-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem;
            padding-bottom: 5rem;
            /* Space for bottom nav bar */
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title span {
            font-size: 0.75rem;
            background: rgba(255, 255, 255, 0.08);
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            color: var(--text-secondary);
        }

        /* Alerts */
        .toast-alert {
            padding: 0.85rem;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toast-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid var(--success);
            color: #a7f3d0;
        }

        /* Task Cards */
        .task-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.15rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .task-card:hover {
            background: var(--surface-hover);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .task-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .task-ref {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text-primary);
        }

        .badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .badge-assigned {
            background: rgba(99, 102, 241, 0.15);
            color: var(--accent-secondary);
            border: 1px solid rgba(99, 102, 241, 0.3);
        }

        .badge-arrived {
            background: rgba(59, 130, 246, 0.15);
            color: var(--info);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .badge-delivered {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-issue {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .badge-started {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-completed {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-warehouse {
            background: rgba(139, 92, 246, 0.15);
            color: var(--accent-primary);
            border: 1px solid rgba(139, 92, 246, 0.3);
        }

        .task-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.825rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .task-detail i {
            font-size: 1rem;
        }

        /* Collapsible details drawer inside card */
        .task-drawer {
            display: none;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            cursor: default;
        }

        .task-drawer.active {
            display: block;
        }

        .drawer-section {
            margin-bottom: 1rem;
        }

        .drawer-section h4 {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.35rem;
        }

        .drawer-section p,
        .drawer-section div {
            font-size: 0.85rem;
            color: var(--text-primary);
        }

        .sku-row {
            display: flex;
            justify-content: space-between;
            padding: 0.25rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        /* Buttons & Forms */
        .btn {
            width: 100%;
            padding: 0.75rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
            margin-top: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: #ffffff;
            box-shadow: 0 4px 12px var(--accent-glow);
        }

        .btn-success {
            background: var(--success);
            color: #ffffff;
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid var(--danger);
            color: #fca5a5;
        }

        .btn-danger:hover {
            background: var(--danger);
            color: #ffffff;
        }

        .form-input {
            width: 100%;
            padding: 0.65rem 0.85rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.85rem;
            outline: none;
            transition: all 0.2s;
            margin-bottom: 0.75rem;
        }

        .form-input:focus {
            border-color: var(--accent-primary);
        }

        .form-file {
            margin-bottom: 0.75rem;
        }

        .form-file label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }

        .form-file input {
            width: 100%;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        /* Image Previews */
        .image-preview {
            max-width: 100px;
            max-height: 100px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            margin-top: 0.25rem;
        }

        /* Bottom Tab Navigation */
        .bottom-nav {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 65px;
            background: rgba(15, 17, 26, 0.95);
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 100;
        }

        .nav-tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-family: inherit;
            font-size: 0.7rem;
            font-weight: 500;
            cursor: pointer;
            width: 33.3%;
            height: 100%;
            justify-content: center;
            transition: color 0.2s;
        }

        .nav-tab i {
            font-size: 1.35rem;
        }

        .nav-tab.active {
            color: var(--accent-primary);
        }

        .tab-badge {
            position: absolute;
            top: 5px;
            right: 25%;
            background: var(--danger);
            color: #ffffff;
            font-size: 0.65rem;
            padding: 0.1rem 0.35rem;
            border-radius: 10px;
            font-weight: 700;
        }

        .nav-tab-wrapper {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Issue Reporting Modal Overlay (Simplified as inline form) */
        .issue-box {
            background: rgba(239, 68, 68, 0.05);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 0.5rem;
            display: none;
        }

        .issue-box.active {
            display: block;
        }
    </style>
</head>

<body>

    <div class="mobile-frame">
        <!-- App Header -->
        <header class="app-header">
            <div class="driver-info">
                <div class="avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <div class="driver-details">
                    <h2>{{ Auth::user()->name }}</h2>
                    <p>Status: Active Duty</p>
                </div>
            </div>
            <form action="{{ route('driver.logout') }}" method="POST" id="logout-form">
                @csrf
                <button type="submit" class="logout-btn" title="Sign out of Shift">
                    <i class="ph ph-sign-out"></i>
                </button>
            </form>
        </header>

        <!-- App Content Area -->
        <div class="app-content">
            @if(session('success'))
                <div class="toast-alert toast-success">
                    <i class="ph ph-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="toast-alert"
                    style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #fca5a5; flex-direction: column; align-items: flex-start; gap: 0.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                        <i class="ph ph-warning-circle"></i> Upload / Submission Error:
                    </div>
                    <ul style="margin: 0.25rem 0 0 1.25rem; padding: 0; font-size: 0.8rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- ==================== TAB 1: DELIVERIES ==================== -->
            <div id="panel-deliveries" class="tab-panel active">
                <div class="section-title">
                    Deliveries
                    <span>{{ $deliveries->whereIn('delivery_status', ['Assigned', 'Arrived'])->count() }} pending</span>
                </div>

                @if($deliveries->isEmpty())
                    <div class="empty-state">
                        <i class="ph ph-clipboard-text"></i>
                        <p>No delivery runs assigned today.</p>
                    </div>
                @else
                    @foreach($deliveries as $order)
                        @php $cardId = ($order->is_delivery_note ? 'dn-' : 'so-') . $order->id; @endphp
                        <div class="task-card" onclick="toggleDrawer(event, 'delivery-{{ $cardId }}')">
                            <div class="task-card-header">
                                <span class="task-ref">{{ $order->ref_number }}</span>
                                <span class="badge badge-{{ strtolower(str_replace(' ', '-', $order->delivery_status)) }}">
                                    {{ $order->delivery_status }}
                                </span>
                            </div>
                            <div class="task-detail">
                                <i class="ph ph-user"></i>
                                <span>{{ $order->customer_name }}</span>
                            </div>
                            <div class="task-detail">
                                <i class="ph ph-map-pin" style="color: var(--accent-primary);"></i>
                                <span>{{ $order->customer_address && $order->customer_address !== 'N/A' ? $order->customer_address : 'N/A' }}</span>
                            </div>

                            <!-- Expandable Drawer -->
                            <div id="drawer-delivery-{{ $cardId }}" class="task-drawer">
                                <div class="drawer-section">
                                    <h4>{{ $order->is_delivery_note ? 'Delivery Note / DI' : 'Sales Order' }}</h4>
                                    <p>{{ $order->display_so_number }}</p>
                                </div>

                                <div class="drawer-section">
                                    <h4>Products SKU Summary</h4>
                                    @php $totalQty = 0; @endphp
                                    @foreach($order->items as $item)
                                        @php $totalQty += $item->quantity; @endphp
                                        <div class="sku-row">
                                            <span>{{ $item->sku_code }}</span>
                                            <span>x{{ $item->quantity }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="drawer-section">
                                    <h4>Total Quantity</h4>
                                    <p>{{ $totalQty }} units</p>
                                </div>

                                @if($order->delivery_completed_at)
                                    <div class="drawer-section">
                                        <h4>Delivery Time</h4>
                                        <p>{{ $order->delivery_completed_at }}</p>
                                    </div>
                                    <div class="drawer-section">
                                        <h4>Recipient</h4>
                                        <p>{{ $order->recipient_name }}</p>
                                    </div>
                                    <div class="drawer-section">
                                        <h4>Remarks</h4>
                                        <p>{{ $order->delivery_remarks ?? 'None' }}</p>
                                    </div>
                                    @if($order->delivery_photo_path)
                                        <div class="drawer-section">
                                            <h4>Delivery Photo</h4>
                                            <img src="{{ asset($order->delivery_photo_path) }}" class="image-preview"
                                                alt="Delivery Photo">
                                        </div>
                                    @endif
                                    @if($order->signed_proof_path)
                                        <div class="drawer-section">
                                            <h4>Signed Proof</h4>
                                            <img src="{{ asset($order->signed_proof_path) }}" class="image-preview"
                                                alt="Signature Proof">
                                        </div>
                                    @endif
                                @endif

                                @if($order->delivery_status === 'Issue Reported')
                                    <div class="drawer-section" style="color: var(--danger);">
                                        <h4>Reported Issue</h4>
                                        <p>{{ $order->delivery_issue }}</p>
                                    </div>
                                @endif

                                <!-- Execution Actions -->
                                @if($order->delivery_status !== 'Delivered')
                                    <form action="{{ route('driver.deliveries.complete', $cardId) }}" method="POST"
                                        enctype="multipart/form-data" onclick="event.stopPropagation();">
                                        @csrf
                                        <div class="form-group" style="margin-top: 1rem;">
                                            <div class="form-file">
                                                <label for="signed_proof-{{ $cardId }}">Upload Signed Proof (POD)</label>
                                                <input type="file" id="signed_proof-{{ $cardId }}" name="signed_proof"
                                                    accept="image/*,application/pdf,.heic" required
                                                    onclick="event.stopPropagation();">
                                            </div>

                                            <div class="form-file">
                                                <label for="delivery_photo-{{ $cardId }}">Upload Delivery Photo</label>
                                                <input type="file" id="delivery_photo-{{ $cardId }}" name="delivery_photo"
                                                    accept="image/*,application/pdf,.heic" required
                                                    onclick="event.stopPropagation();">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success" onclick="event.stopPropagation();">
                                            <i class="ph ph-check-square"></i> Mark Delivered
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-danger"
                                        onclick="toggleIssueBox(event, 'delivery-issue-{{ $cardId }}')">
                                        <i class="ph ph-warning-octagon"></i> Report Issue
                                    </button>
                                    <div id="delivery-issue-{{ $cardId }}" class="issue-box">
                                        <form action="{{ route('driver.deliveries.issue', $cardId) }}" method="POST">
                                            @csrf
                                            <input type="text" name="delivery_issue" placeholder="Describe the issue"
                                                class="form-input" required>
                                            <button type="submit" class="btn btn-danger">Submit Issue</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- ==================== TAB 2: RETURNS ==================== -->
            <div id="panel-returns" class="tab-panel">
                <div class="section-title">
                    Return Pickups
                    <span>{{ $returns->whereIn('status', ['Pending Pickup', 'Pickup Started', 'Completed'])->count() }}
                        tasks</span>
                </div>

                @if($returns->isEmpty())
                    <div class="empty-state">
                        <i class="ph ph-arrow-u-up-left"></i>
                        <p>No return pickups scheduled.</p>
                    </div>
                @else
                    @foreach($returns as $ret)
                        <div class="task-card" onclick="toggleDrawer(event, 'return-{{ $ret->id }}')">
                            <div class="task-card-header">
                                <span class="task-ref">{{ $ret->return_ref }}</span>
                                <span
                                    class="badge badge-{{ strtolower(str_replace(' ', '-', $ret->status === 'Returned to Warehouse' ? 'warehouse' : ($ret->status === 'Pickup Started' ? 'started' : ($ret->status === 'Completed' ? 'completed' : 'assigned')))) }}">
                                    {{ $ret->status }}
                                </span>
                            </div>
                            <div class="task-detail">
                                <i class="ph ph-map-pin" style="color: var(--accent-primary);"></i>
                                @if($ret->pickup_location && $ret->pickup_location !== 'N/A')
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($ret->pickup_location) }}"
                                        target="_blank" onclick="event.stopPropagation();"
                                        style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <span style="border-bottom: 1px dashed var(--text-secondary); cursor: pointer;"
                                            title="Open in Google Maps">{{ $ret->pickup_location }}</span>
                                        <i class="ph ph-arrow-square-out" style="font-size: 0.85rem; opacity: 0.7;"></i>
                                    </a>
                                @else
                                    <span>N/A</span>
                                @endif
                            </div>
                            <div class="task-detail">
                                <i class="ph ph-tag"></i>
                                <span>{{ $ret->product_sku }} (x{{ $ret->quantity }})</span>
                            </div>

                            <!-- Expandable Drawer -->
                            <div id="drawer-return-{{ $ret->id }}" class="task-drawer">
                                @if($ret->quantity_picked_up !== null)
                                    <div class="drawer-section">
                                        <h4>Quantity Picked Up</h4>
                                        <p>{{ $ret->quantity_picked_up }} units</p>
                                    </div>
                                    <div class="drawer-section">
                                        <h4>Condition Data</h4>
                                        <p>{{ $ret->condition_data }}</p>
                                    </div>
                                    @if($ret->photo_path)
                                        <div class="drawer-section">
                                            <h4>Return Photo</h4>
                                            <img src="{{ asset($ret->photo_path) }}" class="image-preview" alt="Return Photo">
                                        </div>
                                    @endif
                                    @if($ret->remarks)
                                        <div class="drawer-section">
                                            <h4>Remarks</h4>
                                            <p>{{ $ret->remarks }}</p>
                                        </div>
                                    @endif
                                @endif

                                @if($ret->status === 'Pending Pickup')
                                    <form action="{{ route('driver.returns.start', $ret->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ph ph-play-circle"></i> Mark Pickup Started
                                        </button>
                                    </form>
                                @elseif($ret->status === 'Pickup Started')
                                    <form action="{{ route('driver.returns.complete', $ret->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group" style="margin-top: 1rem;">
                                            <label class="form-label"
                                                style="font-size: 0.75rem; color: var(--text-secondary);">Quantity Picked Up</label>
                                            <input type="number" name="quantity_picked_up" value="{{ $ret->quantity }}"
                                                class="form-input" required min="0">

                                            <input type="text" name="condition_data"
                                                placeholder="Condition / Classification data (e.g. Damaged box, fully sealed)"
                                                class="form-input" required>
                                            <input type="text" name="remarks" placeholder="Remarks" class="form-input">

                                            <div class="form-file">
                                                <label for="return_photo-{{ $ret->id }}">Upload Photo evidence</label>
                                                <input type="file" id="return_photo-{{ $ret->id }}" name="photo" accept="image/*"
                                                    required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="ph ph-check-circle"></i> Confirm Pickup Completed
                                        </button>
                                    </form>
                                @elseif($ret->status === 'Completed')
                                    <form action="{{ route('driver.returns.handover', $ret->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ph ph-package"></i> Submit Return Handover
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- ==================== TAB 3: CHEQUES ==================== -->
            <div id="panel-cheques" class="tab-panel">
                <div class="section-title">
                    Cheque Collections
                    <span>{{ $cheques->whereIn('status', ['Pending Collection', 'Collected'])->count() }}
                        collections</span>
                </div>

                @if($cheques->isEmpty())
                    <div class="empty-state">
                        <i class="ph ph-bank"></i>
                        <p>No cheque collections assigned.</p>
                    </div>
                @else
                    @foreach($cheques as $chq)
                        <div class="task-card" onclick="toggleDrawer(event, 'cheque-{{ $chq->id }}')">
                            <div class="task-card-header">
                                <span class="task-ref">{{ $chq->collection_ref }}</span>
                                <span
                                    class="badge badge-{{ strtolower(str_replace(' ', '-', $chq->status === 'Pending Collection' ? 'assigned' : ($chq->status === 'Collected' ? 'started' : ($chq->status === 'Submitted' ? 'completed' : 'issue')))) }}">
                                    {{ $chq->status }}
                                </span>
                            </div>
                            <div class="task-detail">
                                <i class="ph ph-user"></i>
                                <span>{{ $chq->customer_name }}</span>
                            </div>
                            <div class="task-detail">
                                <i class="ph ph-map-pin" style="color: var(--accent-primary);"></i>
                                @if($chq->collection_location && $chq->collection_location !== 'N/A')
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($chq->collection_location) }}"
                                        target="_blank" onclick="event.stopPropagation();"
                                        style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <span style="border-bottom: 1px dashed var(--text-secondary); cursor: pointer;"
                                            title="Open in Google Maps">{{ $chq->collection_location }}</span>
                                        <i class="ph ph-arrow-square-out" style="font-size: 0.85rem; opacity: 0.7;"></i>
                                    </a>
                                @else
                                    <span>N/A</span>
                                @endif
                            </div>
                            <div class="task-detail" style="font-weight: 600; color: var(--accent-primary);">
                                <i class="ph ph-currency-dollar"></i>
                                <span>${{ number_format($chq->amount, 2) }}</span>
                            </div>

                            <!-- Expandable Drawer -->
                            <div id="drawer-cheque-{{ $chq->id }}" class="task-drawer">
                                @if($chq->submission_time)
                                    <div class="drawer-section">
                                        <h4>Submission Time</h4>
                                        <p>{{ $chq->submission_time }}</p>
                                    </div>
                                @endif
                                @if($chq->photo_path)
                                    <div class="drawer-section">
                                        <h4>Cheque Photo</h4>
                                        <img src="{{ asset($chq->photo_path) }}" class="image-preview" alt="Cheque Photo">
                                    </div>
                                @endif
                                @if($chq->remarks)
                                    <div class="drawer-section">
                                        <h4>Remarks</h4>
                                        <p>{{ $chq->remarks }}</p>
                                    </div>
                                @endif

                                @if($chq->status === 'Pending Collection')
                                    <form action="{{ route('driver.cheques.collect', $chq->id) }}" method="POST"
                                        enctype="multipart/form-data" onclick="event.stopPropagation();">
                                        @csrf
                                        <div class="form-group" style="margin-top: 1rem;">
                                            <label class="form-label" style="font-size: 0.75rem; color: var(--text-secondary); font-weight: 600;">Enter Paid / Collected Amount (QAR / USD) *</label>
                                            <input type="number" step="0.01" min="0.01" name="amount" value="{{ $chq->amount }}" class="form-input" placeholder="Enter Paid Amount (e.g. 450.00)" required onclick="event.stopPropagation();">

                                            <div class="form-file" style="margin-top: 0.75rem;">
                                                <label for="cheque_photo-{{ $chq->id }}">Upload Cheque Photo *</label>
                                                <input type="file" id="cheque_photo-{{ $chq->id }}" name="photo" accept="image/*,application/pdf,.heic"
                                                    required onclick="event.stopPropagation();">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success" style="margin-top: 1rem;" onclick="event.stopPropagation();">
                                            <i class="ph ph-check-square"></i> Mark Collected
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-danger"
                                        onclick="toggleIssueBox(event, 'cheque-issue-{{ $chq->id }}')">
                                        <i class="ph ph-warning-octagon"></i> Report Issue
                                    </button>
                                    <div id="cheque-issue-{{ $chq->id }}" class="issue-box">
                                        <form action="{{ route('driver.cheques.issue', $chq->id) }}" method="POST">
                                            @csrf
                                            <input type="text" name="remarks"
                                                placeholder="Describe the issue (e.g. Customer not present)" class="form-input"
                                                required>
                                            <button type="submit" class="btn btn-danger">Submit Issue</button>
                                        </form>
                                    </div>
                                @elseif($chq->status === 'Collected')
                                    <form action="{{ route('driver.cheques.submit', $chq->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ph ph-check-circle"></i> Submit Collection
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Bottom Navigation Bar -->
        <nav class="bottom-nav">
            <button class="nav-tab active" onclick="switchTab('deliveries')">
                <div class="nav-tab-wrapper">
                    <i class="ph ph-truck"></i>
                    @php $pendingDelCount = $deliveries->whereIn('delivery_status', ['Assigned', 'Arrived'])->count(); @endphp
                    @if($pendingDelCount > 0)
                        <span class="tab-badge">{{ $pendingDelCount }}</span>
                    @endif
                </div>
                Deliveries
            </button>
            <button class="nav-tab" onclick="switchTab('returns')">
                <div class="nav-tab-wrapper">
                    <i class="ph ph-arrow-u-up-left"></i>
                    @php $pendingRetCount = $returns->whereIn('status', ['Pending Pickup', 'Pickup Started', 'Completed'])->count(); @endphp
                    @if($pendingRetCount > 0)
                        <span class="tab-badge">{{ $pendingRetCount }}</span>
                    @endif
                </div>
                Returns
            </button>
            <button class="nav-tab" onclick="switchTab('cheques')">
                <div class="nav-tab-wrapper">
                    <i class="ph ph-bank"></i>
                    @php $pendingChqCount = $cheques->whereIn('status', ['Pending Collection', 'Collected'])->count(); @endphp
                    @if($pendingChqCount > 0)
                        <span class="tab-badge">{{ $pendingChqCount }}</span>
                    @endif
                </div>
                Cheques
            </button>
        </nav>
    </div>

    <script>
        // Tab switching logic
        function switchTab(tabId) {
            // Toggle tab active styles
            const tabs = document.querySelectorAll('.nav-tab');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Find which button clicked
            const clickedTab = Array.from(tabs).find(tab => tab.textContent.trim().toLowerCase().includes(tabId));
            if (clickedTab) {
                clickedTab.classList.add('active');
            }

            // Toggle panel visibility
            const panels = document.querySelectorAll('.tab-panel');
            panels.forEach(panel => panel.classList.remove('active'));

            document.getElementById('panel-' + tabId).classList.add('active');
        }

        // Toggle expansion of task cards
        function toggleDrawer(event, id) {
            // Avoid collapsing if clicking inside an active drawer form or button
            if (event.target.closest('.task-drawer') && !event.target.classList.contains('task-drawer')) {
                return;
            }

            const drawer = document.getElementById('drawer-' + id);
            if (drawer) {
                drawer.classList.toggle('active');
            }
        }

        // Toggle inline issue reporting boxes
        function toggleIssueBox(event, id) {
            event.stopPropagation(); // Prevent triggering toggleDrawer
            const box = document.getElementById(id);
            if (box) {
                box.classList.toggle('active');
            }
        }
    </script>

</body>

</html>