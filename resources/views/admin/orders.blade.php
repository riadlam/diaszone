@extends('layouts.admin')

@section('title', 'Manage Orders - Admin - DiasZone')

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

        <!-- Orders Table -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6">
            @if($orders->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Order ID</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">User</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Amount</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Date</th>
                            <th class="text-center py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($orders as $order)
                        <tr class="hover:bg-purple-50 transition-colors">
                            <td class="py-4 px-4 text-sm text-gray-900 font-mono">{{ $order['id'] }}</td>
                            <td class="py-4 px-4 text-sm font-semibold text-gray-900">{{ $order['user'] }}</td>
                            <td class="py-4 px-4 text-sm font-bold text-purple-600">{{ number_format(round($order['amount']), 0) }} DZD</td>
                            <td class="py-4 px-4">
                                @php
                                    $status = $order['status'];
                                    $statusClass = 'bg-gray-100 text-gray-800';
                                    $statusText = ucfirst(str_replace('_', ' ', $status));
                                    
                                    if ($status === 'completed') {
                                        $statusClass = 'bg-green-100 text-green-800';
                                    } elseif (in_array($status, ['pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay', 'pending_confirmation'])) {
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($status === 'sending') {
                                        $statusClass = 'bg-blue-100 text-blue-800';
                                    } elseif (in_array($status, ['cancelled', 'refunded'])) {
                                        $statusClass = 'bg-red-100 text-red-800';
                                    }
                                @endphp
                                <span class="px-3 py-1.5 rounded-full text-xs font-bold {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-sm text-gray-600">{{ $order['date']->format('M d, Y H:i') }}</td>
                            <td class="py-4 px-4 text-center">
                                <button onclick="viewOrder('{{ $order['id'] }}')" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                    View
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4">
                @foreach($orders as $order)
                <div class="bg-gradient-to-br from-white to-purple-50/30 rounded-xl border-2 border-purple-100 p-4 shadow-md">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">Order ID</p>
                            <p class="text-sm font-bold text-gray-900 font-mono">{{ $order['id'] }}</p>
                        </div>
                        @php
                            $status = $order['status'];
                            $statusClass = 'bg-gray-100 text-gray-800';
                            $statusText = ucfirst(str_replace('_', ' ', $status));
                            
                            if ($status === 'completed') {
                                $statusClass = 'bg-green-100 text-green-800';
                            } elseif (in_array($status, ['pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay', 'pending_confirmation'])) {
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                            } elseif ($status === 'sending') {
                                $statusClass = 'bg-blue-100 text-blue-800';
                            } elseif (in_array($status, ['cancelled', 'refunded'])) {
                                $statusClass = 'bg-red-100 text-red-800';
                            }
                        @endphp
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </div>
                    
                    <div class="space-y-2 border-t border-gray-200 pt-3">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">User</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $order['user'] }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                            <span class="text-xs text-gray-500">Amount</span>
                            <span class="text-lg font-bold text-purple-600">{{ number_format(round($order['amount']), 0) }} DZD</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Date</span>
                            <span class="text-sm text-gray-600">{{ $order['date']->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="pt-3 border-t border-gray-200">
                            <button onclick="viewOrder('{{ $order['id'] }}')" class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex justify-center">
                {{ $orders->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="text-gray-600 text-lg font-semibold mb-2">No orders found</p>
                @if(request('search') || request('status'))
                <a href="{{ route('admin.orders') }}" class="mt-4 inline-block px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                    Clear filters and view all orders
                </a>
                @endif
            </div>
            @endif
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
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-3xl border-2 border-gray-200 transform transition-all">
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
                
                <!-- Modal Body (Scrollable) -->
                <div class="bg-gray-50 max-h-[70vh] overflow-y-auto">
                    <div id="orderModalContent" class="p-6">
                        <!-- Loading state -->
                        <div class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-purple-600 border-t-transparent"></div>
                            <p class="mt-4 text-gray-600 font-medium">Loading order details...</p>
                        </div>
                    </div>
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
            
            content.innerHTML = `
                <div class="space-y-5">
                    <!-- Order Info Card -->
                    <div class="bg-white rounded-lg border-2 border-gray-200 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-5 py-3 border-b border-gray-200">
                            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Order Information</h4>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Order Number</p>
                                    <p class="text-base font-bold text-gray-900 font-mono">${order.order_number}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</p>
                                    <span class="inline-block px-3 py-1.5 rounded-full text-xs font-bold ${statusClass}">
                                        ${order.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                    </span>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Created At</p>
                                    <p class="text-sm text-gray-900">${order.created_at}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Updated At</p>
                                    <p class="text-sm text-gray-900">${order.updated_at}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Info Card -->
                    <div class="bg-white rounded-lg border-2 border-gray-200 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-5 py-3 border-b border-gray-200">
                            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">User Information</h4>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</p>
                                    <p class="text-base font-semibold text-gray-900">${order.user_name}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</p>
                                    <p class="text-sm text-gray-700 break-all">${order.user_email}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Info Card -->
                    <div class="bg-white rounded-lg border-2 border-gray-200 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-green-50 to-green-100 px-5 py-3 border-b border-gray-200">
                            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Product Information</h4>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Game</p>
                                    <p class="text-base font-semibold text-gray-900">${order.game_name}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pack</p>
                                    <p class="text-sm text-gray-700">${order.pack_name}</p>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Amount</p>
                                <p class="text-2xl font-bold text-purple-600">${Math.round(order.amount).toLocaleString()} DZD</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Game Details Card -->
                    ${getGameDetailsHTML(order)}
                    
                    <!-- Status Update Card -->
                    <div class="bg-white rounded-lg border-2 border-purple-300 shadow-sm overflow-hidden">
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
                </div>
            `;
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
    
    function getGameDetailsHTML(order) {
        let hasDetails = order.user_id_ml || order.zone_id_ml || order.player_id_ff || order.player_id_pubg || order.player_id_hok || order.user_id_bs || order.server_bs || order.notes;
        
        if (!hasDetails) {
            return '';
        }
        
        let html = '<div class="bg-white rounded-lg border-2 border-gray-200 shadow-sm overflow-hidden"><div class="bg-gradient-to-r from-yellow-50 to-yellow-100 px-5 py-3 border-b border-gray-200"><h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Game Details</h4></div><div class="p-5"><div class="space-y-4">';
        
        if (order.user_id_ml || order.zone_id_ml) {
            html += '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
            if (order.user_id_ml) {
                html += `<div class="space-y-1"><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">User ID (ML)</p><p class="text-sm font-mono text-gray-900">${order.user_id_ml}</p></div>`;
            }
            if (order.zone_id_ml) {
                html += `<div class="space-y-1"><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Zone ID (ML)</p><p class="text-sm font-mono text-gray-900">${order.zone_id_ml}</p></div>`;
            }
            html += '</div>';
        }
        
        if (order.player_id_ff) {
            html += `<div class="space-y-1"><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Player ID (Free Fire)</p><p class="text-sm font-mono text-gray-900">${order.player_id_ff}</p></div>`;
        }
        
        if (order.player_id_pubg) {
            html += `<div class="space-y-1"><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Player ID (PUBG)</p><p class="text-sm font-mono text-gray-900">${order.player_id_pubg}</p></div>`;
        }
        
        if (order.player_id_hok) {
            html += `<div class="space-y-1"><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Player ID (Honor of Kings)</p><p class="text-sm font-mono text-gray-900">${order.player_id_hok}</p></div>`;
        }
        
        if (order.user_id_bs || order.server_bs) {
            html += '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
            if (order.user_id_bs) {
                html += `<div class="space-y-1"><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">User ID (Blood Strike)</p><p class="text-sm font-mono text-gray-900">${order.user_id_bs}</p></div>`;
            }
            if (order.server_bs) {
                html += `<div class="space-y-1"><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Server (Blood Strike)</p><p class="text-sm text-gray-900">${order.server_bs}</p></div>`;
            }
            html += '</div>';
        }
        
        if (order.notes) {
            html += `<div class="pt-4 border-t border-gray-200 space-y-1"><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Notes</p><p class="text-sm text-gray-900 whitespace-pre-wrap">${order.notes}</p></div>`;
        }
        
        html += '</div></div></div>';
        return html;
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
                // Update status badge
                const statusBadge = document.querySelector('.bg-gray-50 .rounded-full');
                if (statusBadge) {
                    statusBadge.className = `px-3 py-1.5 rounded-full text-xs font-bold ${getStatusClass(newStatus)}`;
                    statusBadge.textContent = newStatus.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                }
                
                // Show success message
                alert(data.message);
                
                // Reload page to update table
                window.location.reload();
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

