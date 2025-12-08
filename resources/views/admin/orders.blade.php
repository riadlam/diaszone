@extends('layouts.admin')

@section('title', 'Manage Orders - Admin - DiasZone')

@push('styles')
<style>
    /* DataTables Custom Styling */
    #ordersTable_wrapper {
        padding: 1rem;
    }
    
    #ordersTable {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    #ordersTable thead th {
        background: linear-gradient(90deg, #fafafa, #f3f4f6); /* subtle light gradient */
        border-bottom: 2px solid rgba(99,102,241,0.08);
        padding: 0.95rem 1.4rem; /* increased header padding for clarity */
        font-weight: 700;
        font-size: 0.82rem;
        text-transform: uppercase;
        color: #4b5563;
        letter-spacing: 0.05em;
    }

    /* Small helper to give header text a bit of breathing room */
    #ordersTable thead th .th-title {
        display: inline-block;
        padding: 0.125rem 0;
        margin: 0;
        font-size: 0.9rem;
        letter-spacing: 0.04em;
    }

    /* Sticky header with subtle shadow so the header separates from rows */
    #ordersTable thead th {
        position: sticky;
        top: 0;
        z-index: 40;
        box-shadow: 0 10px 30px rgba(2,6,23,0.06), inset 0 -1px 0 rgba(0,0,0,0.02);
        border-top: 2px solid rgba(99,102,241,0.06);
        text-shadow: 0 0.5px 0 rgba(0,0,0,0.04);
    }

    #ordersTable thead th:first-child { border-top-left-radius: 0.5rem; }
    #ordersTable thead th:last-child { border-top-right-radius: 0.5rem; }

    /* Slightly separate row tones so header doesn't blend */
    #ordersTable tbody tr { background: #fff; }
    #ordersTable tbody tr:hover { background: #fbfbfd; }
    
    #ordersTable tbody td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.875rem;
    }
    
    #ordersTable tbody tr:hover {
        background-color: #f9fafb;
    }
    
    /* DataTables Search and Length */
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        margin-left: 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        margin: 0 0.5rem;
    }
    
    /* DataTables Pagination */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin: 0 0.25rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        color: #374151 !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f3f4f6 !important;
        border-color: #9ca3af !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #9333ea !important;
        border-color: #9333ea !important;
        color: white !important;
    }
    
    /* Responsive */
    @media (max-width: 640px) {
        #ordersTable_wrapper .dataTables_length,
        #ordersTable_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
    }
    
    /* Order Details Table Styling */
    #orderDetailsTable_wrapper {
        padding: 0;
    }
    
    #orderDetailsTable {
        border-collapse: separate;
        border-spacing: 0;
        background: white;
    }
    
    #orderDetailsTable thead th {
        background-color: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        padding: 0.75rem 1rem;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        color: #4b5563;
        letter-spacing: 0.05em;
    }
    
    #orderDetailsTable tbody td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.875rem;
    }
    
    #orderDetailsTable tbody tr:hover {
        background-color: #f9fafb;
    }
    
    #orderDetailsTable tbody tr td:first-child {
        font-weight: 600;
        color: #6b7280;
        width: 40%;
    }
    
    #orderDetailsTable tbody tr td:last-child {
        color: #111827;
    }
</style>
@endpush

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Manage Orders</h1>
            <p class="text-gray-600 mt-2 text-sm md:text-base">View and manage all orders</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6 mb-6">
            <form method="GET" action="{{ route('admin.orders') }}" class="space-y-4 md:space-y-0 md:flex md:flex-wrap md:gap-4">
                <div class="flex-1 min-w-full md:min-w-[250px]">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Order ID, user name, or email..." 
                           class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                </div>
                <div class="min-w-full md:min-w-[200px]">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="pending_flexy" {{ request('status') === 'pending_flexy' ? 'selected' : '' }}>Pending Flexy</option>
                        <option value="pending_bmccp" {{ request('status') === 'pending_bmccp' ? 'selected' : '' }}>Pending BMCCP</option>
                        <option value="pending_cryptopay" {{ request('status') === 'pending_cryptopay' ? 'selected' : '' }}>Pending Cryptopay</option>
                        <option value="pending_confirmation" {{ request('status') === 'pending_confirmation' ? 'selected' : '' }}>Pending Confirmation</option>
                        <option value="sending" {{ request('status') === 'sending' ? 'selected' : '' }}>Sending</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="flex gap-3 md:items-end">
                    <button type="submit" class="flex-1 md:flex-none px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                        Filter
                    </button>
                    @if(request('search') || request('status'))
                    <a href="{{ route('admin.orders') }}" class="flex-1 md:flex-none px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors text-center">
                        Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Orders Table with DataTables -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="ordersTable" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Game</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="fixed inset-0 z-50 hidden" style="display: none;">
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" onclick="closeOrderModal()"></div>

    <!-- Modal container -->
    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <!-- Modal panel -->
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-3xl border-2 border-gray-200 transform transition-all max-h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 rounded-t-xl border-b border-purple-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white">Order Details</h3>
                        <button onclick="closeOrderModal()" class="text-white hover:text-gray-200 transition-colors p-1 rounded-lg hover:bg-purple-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Body (Scrollable area) -->
                <div class="flex-1 overflow-auto bg-gray-50">
                    <div id="orderModalContent" class="p-6">
                        <!-- Loading state -->
                        <div class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-purple-600 border-t-transparent"></div>
                            <p class="mt-4 text-gray-600 font-medium">Loading order details...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Order Details Table Container (Hidden initially) -->
                <div id="orderDetailsTableContainer" class="p-6 hidden">
                    <table id="orderDetailsTable" class="display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Modal Footer -->
                <div id="orderModalFooter" class="bg-white px-6 py-4 rounded-b-xl border-t border-gray-200 hidden">
                    <div class="flex justify-end gap-3">
                        <button onclick="closeOrderModal()" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 font-semibold rounded-lg transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables
        var table = $('#ordersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.orders") }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: function(d) {
                    // Add status filter if exists
                    var statusFilter = new URLSearchParams(window.location.search).get('status');
                    if (statusFilter) {
                        d.status = statusFilter;
                    }
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'amount', name: 'amount', orderable: true },
                { data: 'status', name: 'status', orderable: true },
                { data: 'game', name: 'game', orderable: true },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']], // Default sort by Order ID descending
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            responsive: true,
            language: {
                processing: "Loading orders...",
                search: "Search:",
                lengthMenu: "Show _MENU_ orders",
                info: "Showing _START_ to _END_ of _TOTAL_ orders",
                infoEmpty: "No orders found",
                infoFiltered: "(filtered from _MAX_ total orders)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
        
        // Re-draw table when status filter changes
        @if(request('status'))
        table.column(2).search('{{ request("status") }}').draw();
        @endif
    });
    
    function viewOrder(orderNumber) {
        const modal = document.getElementById('orderModal');
        const content = document.getElementById('orderModalContent');
        
        // Show modal
        modal.style.display = 'block';
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Reset content to loading
        content.innerHTML = `
            <div class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-purple-600 border-t-transparent"></div>
                <p class="mt-4 text-gray-600 font-medium">Loading order details...</p>
            </div>
        `;
        
        // Show loading content, hide table container
        content.style.display = 'block';
        document.getElementById('orderDetailsTableContainer').classList.add('hidden');
        
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#orderDetailsTable')) {
            $('#orderDetailsTable').DataTable().destroy();
        }
        
        // Remove status update section if exists
        const statusUpdateSection = document.querySelector('#orderDetailsTableContainer .bg-white.rounded-lg.border-purple-300');
        if (statusUpdateSection) {
            statusUpdateSection.remove();
        }
        
        // Hide footer during loading
        document.getElementById('orderModalFooter').classList.add('hidden');
        
        // Fetch order details
        fetch(`{{ url('/adm/orders') }}/${orderNumber}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            const order = data.order;
            const statusOptions = [
                { value: 'pending', label: 'Pending' },
                { value: 'pending_flexy', label: 'Pending Flexy' },
                { value: 'pending_bmccp', label: 'Pending BMCCP' },
                { value: 'pending_cryptopay', label: 'Pending Cryptopay' },
                { value: 'pending_confirmation', label: 'Pending Confirmation' },
                { value: 'sending', label: 'Sending' },
                { value: 'completed', label: 'Completed' },
                { value: 'cancelled', label: 'Cancelled' },
                { value: 'refunded', label: 'Refunded' }
            ];
            
            const statusClass = getStatusClass(order.status);
            
            // Show footer
            document.getElementById('orderModalFooter').classList.remove('hidden');
            
            // Hide loading content, show table container
            content.style.display = 'none';
            document.getElementById('orderDetailsTableContainer').classList.remove('hidden');
            
            // Prepare data for DataTable
            const tableData = [
                ['Order Number', `<span class="font-mono font-bold">${order.order_number}</span>`],
                ['Status', `<span class="inline-block px-3 py-1.5 rounded-full text-xs font-bold ${statusClass}">${order.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>`],
                ['Created At', order.created_at],
                ['Updated At', order.updated_at],
                ['User Name', `<span class="font-semibold">${order.user_name}</span>`],
                ['User Email', `<span class="break-all">${order.user_email}</span>`],
                ['Game', `<span class="font-semibold">${order.game_name}</span>`],
                ['Pack', order.pack_name],
                ['Amount', `<span class="text-xl font-bold text-purple-600">${Math.round(order.amount).toLocaleString()} DZD</span>`],
            ];
            
            // Add game-specific details
            if (order.user_id_ml) {
                tableData.push(['User ID (Mobile Legends)', `<span class="font-mono">${order.user_id_ml}</span>`]);
            }
            if (order.zone_id_ml) {
                tableData.push(['Zone ID (Mobile Legends)', `<span class="font-mono">${order.zone_id_ml}</span>`]);
            }
            if (order.player_id_ff) {
                tableData.push(['Player ID (Free Fire)', `<span class="font-mono">${order.player_id_ff}</span>`]);
            }
            if (order.player_id_pubg) {
                tableData.push(['Player ID (PUBG Mobile)', `<span class="font-mono">${order.player_id_pubg}</span>`]);
            }
            if (order.player_id_hok) {
                tableData.push(['Player ID (Honor of Kings)', `<span class="font-mono">${order.player_id_hok}</span>`]);
            }
            if (order.user_id_bs) {
                tableData.push(['User ID (Blood Strike)', `<span class="font-mono">${order.user_id_bs}</span>`]);
            }
            if (order.server_bs) {
                tableData.push(['Server (Blood Strike)', order.server_bs]);
            }
            if (order.notes) {
                // Escape HTML to prevent XSS
                const escapedNotes = $('<div>').text(order.notes).html();
                tableData.push(['Notes', `<span class="whitespace-pre-wrap">${escapedNotes}</span>`]);
            }
            if (order.flexy_id) {
                tableData.push(['Flexy ID', order.flexy_id]);
            }
            if (order.bmccp_id) {
                tableData.push(['BMCCP ID', order.bmccp_id]);
            }
            if (order.cryptopay_id) {
                tableData.push(['Cryptopay ID', order.cryptopay_id]);
            }
            
            // Add payment information sections
            if (order.payment_info) {
                // Chargily Payment Info
                if (order.payment_info.chargily) {
                    const chargily = order.payment_info.chargily;
                    tableData.push(['', '<strong class="text-purple-600">--- Chargily Payment ---</strong>']);
                    tableData.push(['Checkout ID', `<span class="font-mono">${chargily.checkout_id}</span>`]);
                    tableData.push(['Status', `<span class="px-2 py-1 rounded text-xs font-semibold ${chargily.status === 'paid' ? 'bg-green-100 text-green-800' : chargily.status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'}">${chargily.status}</span>`]);
                    tableData.push(['Event Type', chargily.event_type || 'N/A']);
                    tableData.push(['Amount', `${parseFloat(chargily.amount).toLocaleString()} DZD`]);
                    if (chargily.fees) {
                        tableData.push(['Fees', `${parseFloat(chargily.fees).toLocaleString()} DZD`]);
                    }
                    if (chargily.payment_method) {
                        tableData.push(['Payment Method', chargily.payment_method]);
                    }
                    tableData.push(['Created At', chargily.created_at]);
                    tableData.push(['Updated At', chargily.updated_at]);
                }
                
                // VIP Reseller Status Info
                if (order.payment_info.vip_reseller) {
                    const vip = order.payment_info.vip_reseller;
                    tableData.push(['', '<strong class="text-blue-600">--- VIP Reseller Status ---</strong>']);
                    tableData.push(['Transaction ID', `<span class="font-mono">${vip.trxid || 'N/A'}</span>`]);
                    tableData.push(['Status', `<span class="px-2 py-1 rounded text-xs font-semibold ${vip.status === 'success' ? 'bg-green-100 text-green-800' : vip.status === 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'}">${vip.status}</span>`]);
                    tableData.push(['Data', vip.data || 'N/A']);
                    tableData.push(['Zone', vip.zone || 'N/A']);
                    tableData.push(['Service', vip.service || 'N/A']);
                    if (vip.note) {
                        // Escape HTML to prevent XSS
                        const escapedNote = $('<div>').text(vip.note).html();
                        tableData.push(['Note', escapedNote]);
                    }
                    if (vip.price) {
                        tableData.push(['Price', `${parseFloat(vip.price).toLocaleString()}`]);
                    }
                    tableData.push(['Created At', vip.created_at]);
                    tableData.push(['Updated At', vip.updated_at]);
                }
                
                // Flexy Payment Info
                if (order.payment_info.flexy) {
                    const flexy = order.payment_info.flexy;
                    tableData.push(['', '<strong class="text-green-600">--- Flexy Payment ---</strong>']);
                    tableData.push(['Flexy ID', flexy.id]);
                    tableData.push(['Status', `<span class="px-2 py-1 rounded text-xs font-semibold ${flexy.status === 'approved' ? 'bg-green-100 text-green-800' : flexy.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'}">${flexy.status || 'pending'}</span>`]);
                    if (flexy.receipt_image) {
                        tableData.push(['Receipt Image', `<a href="${flexy.receipt_image}" target="_blank" class="text-blue-600 hover:underline">View Receipt</a>`]);
                    }
                    tableData.push(['Created At', flexy.created_at]);
                    tableData.push(['Updated At', flexy.updated_at]);
                }
                
                // BMCCP Payment Info (old Chargily v1)
                if (order.payment_info.bmccp) {
                    const bmccp = order.payment_info.bmccp;
                    tableData.push(['', '<strong class="text-orange-600">--- BMCCP Payment (Legacy) ---</strong>']);
                    tableData.push(['BMCCP ID', bmccp.id]);
                    tableData.push(['Status', `<span class="px-2 py-1 rounded text-xs font-semibold ${bmccp.status === 'approved' ? 'bg-green-100 text-green-800' : bmccp.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'}">${bmccp.status || 'pending'}</span>`]);
                    if (bmccp.invoice_number) {
                        tableData.push(['Invoice Number', bmccp.invoice_number]);
                    }
                    if (bmccp.receipt_image) {
                        tableData.push(['Receipt Image', `<a href="${bmccp.receipt_image}" target="_blank" class="text-blue-600 hover:underline">View Receipt</a>`]);
                    }
                    if (bmccp.notes) {
                        // Escape HTML to prevent XSS
                        const escapedNotes = $('<div>').text(bmccp.notes).html();
                        tableData.push(['Notes', escapedNotes]);
                    }
                    tableData.push(['Created At', bmccp.created_at]);
                    tableData.push(['Updated At', bmccp.updated_at]);
                }
                
                // Cryptopay Payment Info
                if (order.payment_info.cryptopay) {
                    const cryptopay = order.payment_info.cryptopay;
                    tableData.push(['', '<strong class="text-indigo-600">--- Cryptopay Payment ---</strong>']);
                    tableData.push(['Cryptopay ID', cryptopay.id]);
                    tableData.push(['Payment ID', cryptopay.payment_id || 'N/A']);
                    tableData.push(['Transaction ID', cryptopay.transaction_id || 'N/A']);
                    tableData.push(['Status', `<span class="px-2 py-1 rounded text-xs font-semibold ${cryptopay.status === 'paid' ? 'bg-green-100 text-green-800' : cryptopay.status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'}">${cryptopay.status || 'pending'}</span>`]);
                    if (cryptopay.amount) {
                        tableData.push(['Amount', `${parseFloat(cryptopay.amount).toLocaleString()} ${cryptopay.currency || 'USD'}`]);
                    }
                    tableData.push(['Created At', cryptopay.created_at]);
                    tableData.push(['Updated At', cryptopay.updated_at]);
                }
            }
            
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#orderDetailsTable')) {
                $('#orderDetailsTable').DataTable().destroy();
            }
            
            // Initialize DataTable
            $('#orderDetailsTable').DataTable({
                data: tableData,
                columns: [
                    { title: 'Field', className: 'font-semibold text-gray-700' },
                    { title: 'Value', className: 'text-gray-900' }
                ],
                paging: false,
                searching: false,
                ordering: false,
                info: false,
                responsive: true,
                scrollX: true,
                language: {
                    emptyTable: 'No data available'
                }
            });
            
            // Add status update section below table
            const statusUpdateHTML = `
                <div class="mt-6 bg-white rounded-lg border-2 border-purple-300 shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-5 py-3 border-b border-purple-200">
                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Update Status</h4>
                    </div>
                    <div class="p-5">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <select id="statusSelect" class="flex-1 px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 font-medium">
                                ${statusOptions.map(opt => 
                                    `<option value="${opt.value}" ${opt.value === order.status ? 'selected' : ''}>${opt.label}</option>`
                                ).join('')}
                            </select>
                            <button onclick="updateOrderStatus('${order.order_number}')" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                                Update Status
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Append status update section
            document.getElementById('orderDetailsTableContainer').insertAdjacentHTML('beforeend', statusUpdateHTML);
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-red-600">Error loading order details. Please try again.</p>
                </div>
            `;
        });
    }
    
    function getStatusClass(status) {
        if (status === 'completed') {
            return 'bg-green-100 text-green-800';
        } else if (['pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay', 'pending_confirmation'].includes(status)) {
            return 'bg-yellow-100 text-yellow-800';
        } else if (status === 'sending') {
            return 'bg-blue-100 text-blue-800';
        } else if (['cancelled', 'refunded'].includes(status)) {
            return 'bg-red-100 text-red-800';
        }
        return 'bg-gray-100 text-gray-800';
    }
    
    
    function updateOrderStatus(orderNumber) {
        const statusSelect = document.getElementById('statusSelect');
        const newStatus = statusSelect.value;
        
        if (!newStatus) {
            alert('Please select a status');
            return;
        }
        
        // Disable button during update
        const updateBtn = event.target;
        updateBtn.disabled = true;
        updateBtn.textContent = 'Updating...';
        
        fetch(`{{ url('/adm/orders') }}/${orderNumber}/status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                status: newStatus,
                from_admin: true  // Flag to ensure this is from admin dashboard UI
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update status in DataTable
                const table = $('#orderDetailsTable').DataTable();
                table.rows().every(function() {
                    const data = this.data();
                    if (data[0] === 'Status') {
                        const statusClass = getStatusClass(newStatus);
                        table.cell(this, 1).data(`<span class="inline-block px-3 py-1.5 rounded-full text-xs font-bold ${statusClass}">${newStatus.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>`);
                        return false; // Stop iteration
                    }
                });
                
                // Update status select dropdown
                document.getElementById('statusSelect').value = newStatus;
                
                // Show success message
                alert(data.message);
                
                // Reload main orders table
                $('#ordersTable').DataTable().ajax.reload(null, false);
            } else {
                alert('Error updating status: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating order status. Please try again.');
        })
        .finally(() => {
            updateBtn.disabled = false;
            updateBtn.textContent = 'Update';
        });
    }
    
    function closeOrderModal() {
        const modal = document.getElementById('orderModal');
        modal.style.display = 'none';
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeOrderModal();
        }
    });
</script>
@endpush
@endsection

