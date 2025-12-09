@extends('layouts.seller')

@section('title', 'Orders - Seller Panel')
@section('header', 'Orders')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
<style>
    /* Custom DataTables styling for dark theme */
    .dataTables_wrapper {
        color: #9ca3af;
    }
    
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        background-color: #334155;
        border: 1px solid #475569;
        border-radius: 0.5rem;
        color: white;
        padding: 0.5rem 1rem;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: #3b82f6;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        background-color: #334155;
        border: 1px solid #475569;
        border-radius: 0.375rem;
        color: #9ca3af !important;
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background-color: #475569 !important;
        color: white !important;
        border-color: #3b82f6;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(to right, #3b82f6, #06b6d4) !important;
        border-color: transparent;
        color: white !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    table.dataTable thead th {
        background: linear-gradient(90deg, rgba(31,41,55,0.98), rgba(45,55,72,0.98));
        color: #f8fafc; /* brighter header text */
        font-weight: 800;
        font-size: 0.95rem;
        line-height: 1.2;
        padding: 1.05rem 1.75rem; /* increase size and spacing to make header stand out */
        border-bottom: 2px solid rgba(99,102,241,0.12);
        vertical-align: middle;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.04em;

        /* make header stand out and remain visible on scroll */
        position: sticky;
        top: 0;
        z-index: 50;
        box-shadow: 0 10px 26px rgba(2,6,23,0.65), inset 0 -2px 0 rgba(255,255,255,0.02);
        text-shadow: 0 1px 0 rgba(0,0,0,0.25);
        border-top: 3px solid rgba(59,130,246,0.08);
        backdrop-filter: blur(4px);
    }

    /* Add small spacing inside header titles for readability */
    table.dataTable thead th .th-title {
        display: inline-block;
        margin: 0; /* avoid extra outer margins */
        padding: 0.15rem 0; /* tiny inner padding for visual breathing room */
        font-size: 0.98rem;
        letter-spacing: 0.06em;
        opacity: 0.98;
    }

    /* Make first and last header slightly rounded so they stand out from rows */
    table.dataTable thead th:first-child { border-top-left-radius: 0.5rem; }
    table.dataTable thead th:last-child { border-top-right-radius: 0.5rem; }

    /* Slightly differentiate row background so it doesn't blend with header */
    table.dataTable tbody tr { background: rgba(255,255,255,0.02); }
    table.dataTable tbody tr:hover { background: rgba(255,255,255,0.03); }
    
    table.dataTable tbody td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #334155;
    }
    
    table.dataTable tbody tr:hover {
        background-color: rgba(51, 65, 85, 0.5);
    }
    
    /* Modal styles */
    .modal-overlay {
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(4px);
    }
    
    .modal-container {
        animation: slideUp 0.2s ease-out;
    }
    
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Action buttons */
    .action-btn {
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .action-btn svg {
        width: 0.875rem;
        height: 0.875rem;
    }
    
    .action-btn-view {
        background: rgba(59, 130, 246, 0.2);
        color: #60a5fa;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }
    
    .action-btn-view:hover {
        background: rgba(59, 130, 246, 0.3);
    }
    
    .action-btn-confirm {
        background: rgba(34, 197, 94, 0.2);
        color: #4ade80;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }
    
    .action-btn-confirm:hover {
        background: rgba(34, 197, 94, 0.3);
    }
    
    .action-btn-delete {
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    
    .action-btn-delete:hover {
        background: rgba(239, 68, 68, 0.3);
    }
    
    /* Toast notification */
    .toast {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
        z-index: 100;
        animation: toastIn 0.3s ease-out;
    }
    
    .toast-success {
        background: rgba(34, 197, 94, 0.9);
        color: white;
    }
    
    .toast-error {
        background: rgba(239, 68, 68, 0.9);
        color: white;
    }
    
    @keyframes toastIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
        <p class="text-gray-400 text-sm">Total Orders</p>
        <p class="text-2xl font-bold text-white">{{ $orders->total() }}</p>
    </div>
    <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
        <p class="text-gray-400 text-sm">Pending Verification</p>
        <p class="text-2xl font-bold text-yellow-400">{{ $pendingFlexyCount ?? 0 }}</p>
    </div>
    <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
        <p class="text-gray-400 text-sm">Processing</p>
        <p class="text-2xl font-bold text-blue-400">{{ $processingCount ?? 0 }}</p>
    </div>
    <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
        <p class="text-gray-400 text-sm">Completed</p>
        <p class="text-2xl font-bold text-green-400">{{ $completedCount ?? 0 }}</p>
    </div>
</div>

<!-- Quick Filters -->
<div class="bg-slate-800 rounded-xl p-4 mb-6 border border-slate-700">
    <div class="flex flex-wrap gap-2">
        <button onclick="filterStatus('')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-slate-700 text-gray-300 hover:bg-slate-600' }}" data-status="">
            All
        </button>
        <button onclick="filterStatus('pending_flexy_verification')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition {{ request('status') === 'pending_flexy_verification' ? 'bg-orange-600 text-white' : 'bg-slate-700 text-gray-300 hover:bg-slate-600' }}" data-status="pending_flexy_verification">
            🔄 Flexy Verification
        </button>
        <button onclick="filterStatus('pending')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition {{ request('status') === 'pending' ? 'bg-yellow-600 text-white' : 'bg-slate-700 text-gray-300 hover:bg-slate-600' }}" data-status="pending">
            Pending
        </button>
        <button onclick="filterStatus('processing')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition {{ request('status') === 'processing' ? 'bg-blue-600 text-white' : 'bg-slate-700 text-gray-300 hover:bg-slate-600' }}" data-status="processing">
            Processing
        </button>
        <button onclick="filterStatus('completed')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition {{ request('status') === 'completed' ? 'bg-green-600 text-white' : 'bg-slate-700 text-gray-300 hover:bg-slate-600' }}" data-status="completed">
            Completed
        </button>
        <button onclick="filterStatus('failed')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition {{ request('status') === 'failed' ? 'bg-red-600 text-white' : 'bg-slate-700 text-gray-300 hover:bg-slate-600' }}" data-status="failed">
            Failed
        </button>
    </div>
</div>

<!-- Orders Table -->
<div class="bg-slate-800 rounded-xl overflow-hidden border border-slate-700">
    <div class="overflow-x-auto">
        <table id="orders-table" class="w-full">
            <thead>
                <tr class="bg-slate-700">
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-300">Order</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-300">Pack</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">Payment Method</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">Price</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">Status</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">Date</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($orders as $order)
                    @php
                        $playerId = $order->user_id_ml ?? $order->player_id_ff ?? $order->player_id_pubg ?? $order->player_id_hok ?? $order->user_id_bs;
                        $zoneId = $order->zone_id_ml ?? $order->server_bs ?? null;
                        $status = $order->status;
                        $statusClass = 'bg-red-500/20 text-red-400';
                        $statusLabel = ucfirst(str_replace('_', ' ', $status));
                        if ($status === 'completed') $statusClass = 'bg-green-500/20 text-green-400';
                        elseif ($status === 'pending') $statusClass = 'bg-yellow-500/20 text-yellow-400';
                        elseif ($status === 'pending_flexy_verification') {
                            $statusClass = 'bg-orange-500/20 text-orange-400';
                            $statusLabel = 'Flexy Pending';
                        }
                        elseif ($status === 'processing') $statusClass = 'bg-blue-500/20 text-blue-400';
                    @endphp
                    <tr data-order="{{ $order->order_number }}" data-status="{{ $status }}" class="hover:bg-slate-700/50">
                        <td class="px-4 py-4">
                            <p class="font-medium text-white text-sm">{{ $order->order_number }}</p>
                            @if($order->is_direct_topup)
                                <span class="inline-block px-2 py-0.5 text-xs rounded bg-purple-500/20 text-purple-400 mt-1">Direct</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-white text-sm">{{ $order->diamondPack->name ?? 'N/A' }}</p>
                            <p class="text-gray-500 text-xs">{{ ucfirst($order->diamondPack->game_type ?? '') }}</p>
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if(strtolower($order->payment_method ?? '') === 'flexy')
                                <div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full bg-orange-600 text-white text-xs font-semibold uppercase tracking-wide">Flexy</span>
                                    @if($order->flexy_receipt)
                                        <a href="{{ asset('storage/' . $order->flexy_receipt) }}" target="_blank" class="text-cyan-400 hover:text-cyan-300 text-xs">Receipt</a>
                                    @endif
                                </div>
                            @else
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-700 text-gray-200 text-xs font-semibold">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            <p class="text-white font-medium text-sm">{{ number_format($order->final_price ?? 0) }} DZD</p>
                            <p class="text-green-400 text-xs">+{{ number_format($order->seller_profit ?? 0) }}</p>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-block px-3 py-1 text-xs rounded-full font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-4 py-4 text-center text-gray-400 text-sm">
                            {{ $order->created_at->format('M d, H:i') }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <!-- View Button -->
                                <button onclick="viewOrder('{{ $order->order_number }}')" class="action-btn action-btn-view" title="View Details">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                
                                <!-- Confirm Button (only for pending_flexy_verification) -->
                                @if($status === 'pending_flexy_verification')
                                    <button onclick="confirmOrder('{{ $order->order_number }}')" class="action-btn action-btn-confirm" title="Confirm & Process">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                @endif
                                
                                <!-- Delete Button (only for pending/failed orders) -->
                                @if(in_array($status, ['pending', 'pending_flexy_verification', 'failed']))
                                    <button onclick="deleteOrder('{{ $order->order_number }}')" class="action-btn action-btn-delete" title="Delete Order">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="text-gray-400">No orders found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($orders->total() > 0)
        <div class="p-4 border-t border-slate-700 flex items-center justify-between gap-4">
            <div class="text-sm text-gray-400">
                Showing <strong class="text-white">{{ $orders->firstItem() ?? 0 }}</strong>
                to <strong class="text-white">{{ $orders->lastItem() ?? 0 }}</strong>
                of <strong class="text-white">{{ $orders->total() }}</strong> orders
            </div>

            <div class="flex-shrink-0">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    @else
        <div class="p-4 border-t border-slate-700 text-center text-gray-500">No orders to display.</div>
    @endif
</div>

<!-- View Order Modal -->
<div id="view-modal" class="fixed inset-0 z-50 hidden">
    <div class="modal-overlay absolute inset-0" onclick="closeViewModal()"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-container relative w-full max-w-lg bg-slate-800 border border-slate-600 rounded-xl shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Order Details</h3>
                    <button onclick="closeViewModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="view-modal-content" class="p-6 overflow-auto max-h-[70vh]">
                <div class="flex items-center justify-center py-8">
                    <svg class="w-8 h-8 animate-spin text-blue-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Order Modal -->
<div id="confirm-modal" class="fixed inset-0 z-50 hidden">
    <div class="modal-overlay absolute inset-0" onclick="closeConfirmModal()"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-container relative w-full max-w-md bg-slate-800 border border-slate-600 rounded-xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
                <h3 class="text-lg font-bold text-white">Confirm Order</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-300 mb-4">Are you sure you want to confirm this order?</p>
                <p class="text-yellow-400 text-sm mb-4">⚠️ This will process the order and deduct from your wallet.</p>
                <p class="text-white font-medium mb-6">Order: <span id="confirm-order-number" class="text-cyan-400">-</span></p>
                <div class="flex gap-3">
                    <button onclick="closeConfirmModal()" class="flex-1 px-4 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-medium transition">
                        Cancel
                    </button>
                    <button id="confirm-btn" onclick="processConfirm()" class="flex-1 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-bold transition flex items-center justify-center gap-2">
                        <span>Confirm</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Order Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 hidden">
    <div class="modal-overlay absolute inset-0" onclick="closeDeleteModal()"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-container relative w-full max-w-md bg-slate-800 border border-slate-600 rounded-xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-red-600 to-rose-600 px-6 py-4">
                <h3 class="text-lg font-bold text-white">Delete Order</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-300 mb-4">Are you sure you want to delete this order?</p>
                <p class="text-red-400 text-sm mb-4">⚠️ This action cannot be undone.</p>
                <p class="text-white font-medium mb-6">Order: <span id="delete-order-number" class="text-cyan-400">-</span></p>
                <div class="flex gap-3">
                    <button onclick="closeDeleteModal()" class="flex-1 px-4 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-medium transition">
                        Cancel
                    </button>
                    <button id="delete-btn" onclick="processDelete()" class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-lg font-bold transition flex items-center justify-center gap-2">
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    const csrfToken = '{{ csrf_token() }}';
    let currentOrderNumber = null;
    
    // Filter by status
    function filterStatus(status) {
        const url = new URL(window.location.href);
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        window.location.href = url.toString();
    }
    
    // Toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // View Order Modal
    function viewOrder(orderNumber) {
        currentOrderNumber = orderNumber;
        document.getElementById('view-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Fetch order details
        fetch(`{{ url('seller/orders') }}/${orderNumber}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderOrderDetails(data.order);
            } else {
                document.getElementById('view-modal-content').innerHTML = `
                    <p class="text-red-400 text-center">Failed to load order details</p>
                `;
            }
        })
        .catch(err => {
            document.getElementById('view-modal-content').innerHTML = `
                <p class="text-red-400 text-center">Error loading order details</p>
            `;
        });
    }
    
    function renderOrderDetails(order) {
        const playerId = order.user_id_ml || order.player_id_ff || order.player_id_pubg || order.player_id_hok || order.user_id_bs || '-';
        const zoneId = order.zone_id_ml || order.server_bs || '-';
        
        let statusClass = 'bg-gray-500/20 text-gray-400';
        if (order.status === 'completed') statusClass = 'bg-green-500/20 text-green-400';
        else if (order.status === 'pending') statusClass = 'bg-yellow-500/20 text-yellow-400';
        else if (order.status === 'pending_flexy_verification') statusClass = 'bg-orange-500/20 text-orange-400';
        else if (order.status === 'processing') statusClass = 'bg-blue-500/20 text-blue-400';
        else if (order.status === 'failed') statusClass = 'bg-red-500/20 text-red-400';
        
        let receiptHtml = '';
        if (order.flexy_receipt) {
            receiptHtml = `
                <div class="mt-4 p-4 bg-slate-700/50 rounded-lg">
                    <p class="text-gray-400 text-sm mb-2">Flexy Receipt:</p>
                    <a href="/storage/${order.flexy_receipt}" target="_blank" class="text-cyan-400 hover:text-cyan-300 underline text-sm">View Receipt Image</a>
                    ${order.flexy_description ? `<p class="text-gray-300 text-sm mt-2">${order.flexy_description}</p>` : ''}
                </div>
            `;
        }
        
        document.getElementById('view-modal-content').innerHTML = `
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Order Number</span>
                    <span class="text-white font-mono">${order.order_number}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Status</span>
                    <span class="inline-block px-3 py-1 text-xs rounded-full ${statusClass}">${order.status.replace(/_/g, ' ')}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Pack</span>
                    <span class="text-white">${order.diamond_pack?.name || 'N/A'}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Game</span>
                    <span class="text-white">${order.diamond_pack?.game_type || 'N/A'}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Player ID</span>
                    <span class="text-cyan-400 font-mono">${playerId}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Zone ID</span>
                    <span class="text-white">${zoneId}</span>
                </div>
                <div class="border-t border-slate-600 pt-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400">Price</span>
                        <span class="text-white font-bold">${Number(order.final_price).toLocaleString()} DZD</span>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-gray-400">Your Cost</span>
                        <span class="text-gray-300">${Number(order.seller_cost).toLocaleString()} DZD</span>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-gray-400">Your Profit</span>
                        <span class="text-green-400 font-bold">+${Number(order.seller_profit).toLocaleString()} DZD</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Payment Method</span>
                    <span class="text-white">${order.payment_method || 'N/A'}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Date</span>
                    <span class="text-white">${new Date(order.created_at).toLocaleString()}</span>
                </div>
                ${receiptHtml}
            </div>
        `;
    }
    
    function closeViewModal() {
        document.getElementById('view-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    // Confirm Order Modal
    function confirmOrder(orderNumber) {
        currentOrderNumber = orderNumber;
        document.getElementById('confirm-order-number').textContent = orderNumber;
        document.getElementById('confirm-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeConfirmModal() {
        document.getElementById('confirm-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    function processConfirm() {
        const btn = document.getElementById('confirm-btn');
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Processing...</span>
        `;
        
        fetch(`{{ url('seller/orders') }}/${currentOrderNumber}/confirm`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show immediate feedback and update UI row
                showToast('Top-up requested — processing with VIP reseller...', 'success');
                closeConfirmModal();

                // Update row status to processing
                const row = document.querySelector(`[data-order="${currentOrderNumber}"]`);
                if (row) {
                    row.dataset.status = 'processing';
                    const statusEl = row.querySelector('td:nth-child(5) span');
                    if (statusEl) {
                        statusEl.className = 'inline-block px-3 py-1 text-xs rounded-full font-medium bg-blue-500/20 text-blue-400';
                        statusEl.textContent = 'Processing';
                    }
                }

                // If server returned the final order and seller info, update the row fully
                if (data.order) {
                    // Update price and profit
                    if (row) {
                        const priceCell = row.querySelector('td:nth-child(4)');
                        if (priceCell) {
                            priceCell.querySelector('p')?.textContent = Number(data.order.final_price).toLocaleString() + ' DZD';
                            // profit show
                            priceCell.querySelectorAll('p')[1] && (priceCell.querySelectorAll('p')[1].textContent = '+' + Number(data.order.seller_profit).toLocaleString());
                        }
                    }

                    // If seller info present show wallet change snackbar
                    if (data.seller) {
                        const before = Number(data.seller.wallet_before).toLocaleString();
                        const after = Number(data.seller.wallet_after).toLocaleString();
                        showToast(`Wallet changed: ${before} → ${after} DZD`, 'success');
                    }

                    // Update row dataset to completed
                    if (row) {
                        row.dataset.status = data.order.status || 'completed';
                        const statusEl = row.querySelector('td:nth-child(5) span');
                        if (statusEl) {
                            statusEl.className = 'inline-block px-3 py-1 text-xs rounded-full font-medium bg-green-500/20 text-green-400';
                            statusEl.textContent = 'Completed';
                        }
                    }
                }

                // Re-enable UI state after a little while
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<span>Confirm</span>';
                }, 1000);
            } else {
                // Failed to process: show message and update row status to failed
                showToast(data.message || 'Failed to confirm order', 'error');
                const row = document.querySelector(`[data-order="${currentOrderNumber}"]`);
                if (row) {
                    row.dataset.status = 'failed';
                    const statusEl = row.querySelector('td:nth-child(5) span');
                    if (statusEl) {
                        statusEl.className = 'inline-block px-3 py-1 text-xs rounded-full font-medium bg-red-500/20 text-red-400';
                        statusEl.textContent = 'Failed';
                    }
                }
                btn.disabled = false;
                btn.innerHTML = '<span>Confirm</span>';
            }
        })
        .catch(err => {
            showToast('An error occurred', 'error');
            btn.disabled = false;
            btn.innerHTML = '<span>Confirm</span>';
        });
    }
    
    // Delete Order Modal
    function deleteOrder(orderNumber) {
        currentOrderNumber = orderNumber;
        document.getElementById('delete-order-number').textContent = orderNumber;
        document.getElementById('delete-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    function processDelete() {
        const btn = document.getElementById('delete-btn');
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Deleting...</span>
        `;
        
        fetch(`{{ url('seller/orders') }}/${currentOrderNumber}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Order deleted successfully!', 'success');
                closeDeleteModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to delete order', 'error');
                btn.disabled = false;
                btn.innerHTML = '<span>Delete</span>';
            }
        })
        .catch(err => {
            showToast('An error occurred', 'error');
            btn.disabled = false;
            btn.innerHTML = '<span>Delete</span>';
        });
    }
    
    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeViewModal();
            closeConfirmModal();
            closeDeleteModal();
        }
    });
    
    // Initialize DataTables (optional, for enhanced filtering)
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
            jQuery('#orders-table').DataTable({
                paging: false, // Using Laravel pagination
                searching: true,
                ordering: true,
                info: false,
                order: [[5, 'desc']], // Order by date desc (adjusted after removing Payment column)
                columnDefs: [
                    { orderable: false, targets: [6] } // Disable ordering on actions column (new index)
                ]
            });
        }
    });
</script>
@endpush
