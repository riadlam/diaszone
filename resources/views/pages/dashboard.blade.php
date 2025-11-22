@extends('layouts.app')

@section('title', 'Dashboard - DiasZone')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="container mx-auto px-4 max-w-7xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold text-purple-600 mb-4">
                Dashboard
            </h1>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start">
            <!-- Sidebar Navigation - Fixed on Left -->
            <aside id="dashboard-sidebar" class="w-full lg:w-64 flex-shrink-0 lg:order-1">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <nav class="space-y-2">
                        <a href="{{ route('dashboard.orders') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all {{ $activeSection === 'orders' ? 'bg-purple-600 text-white shadow-md' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span class="font-medium">My Orders</span>
                        </a>
                        <a href="{{ route('dashboard.invoices') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all {{ $activeSection === 'invoices' ? 'bg-purple-600 text-white shadow-md' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="font-medium">Invoices</span>
                        </a>
                        <a href="{{ route('dashboard.notifications') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all {{ $activeSection === 'notifications' ? 'bg-purple-600 text-white shadow-md' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="font-medium">Notifications</span>
                        </a>
                        <a href="{{ route('dashboard.myaccount') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all {{ $activeSection === 'account' ? 'bg-purple-600 text-white shadow-md' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="font-medium">My Account</span>
                        </a>
                    </nav>
                </div>
            </aside>
            
            <!-- Spacer to maintain layout when sidebar becomes sticky -->
            <div id="sidebar-spacer" class="hidden lg:block w-64 flex-shrink-0"></div>

            <!-- Content -->
            <div class="flex-1 min-w-0 lg:order-2">
                <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
                    @if($activeSection === 'orders')
                        <!-- Orders Section -->
                        <div id="orders-section">
                            <h2 class="text-3xl font-bold text-gray-900 mb-6">My Orders</h2>
                            <div id="orders-loading" class="space-y-4">
                                <!-- Skeleton Loading Cards -->
                                <div class="border-2 border-gray-200 rounded-xl p-6 animate-pulse">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex-1">
                                            <div class="h-6 bg-gray-200 rounded w-32 mb-3"></div>
                                            <div class="h-5 bg-gray-200 rounded w-40 mb-2"></div>
                                            <div class="h-4 bg-gray-200 rounded w-24 mb-2"></div>
                                            <div class="h-4 bg-gray-200 rounded w-24"></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="h-7 bg-gray-200 rounded w-24 mb-3"></div>
                                            <div class="h-6 bg-gray-200 rounded w-20"></div>
                                        </div>
                                    </div>
                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="h-4 bg-gray-200 rounded w-40"></div>
                                    </div>
                                </div>
                                <div class="border-2 border-gray-200 rounded-xl p-6 animate-pulse">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex-1">
                                            <div class="h-6 bg-gray-200 rounded w-32 mb-3"></div>
                                            <div class="h-5 bg-gray-200 rounded w-40 mb-2"></div>
                                            <div class="h-4 bg-gray-200 rounded w-24 mb-2"></div>
                                            <div class="h-4 bg-gray-200 rounded w-24"></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="h-7 bg-gray-200 rounded w-24 mb-3"></div>
                                            <div class="h-6 bg-gray-200 rounded w-20"></div>
                                        </div>
                                    </div>
                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="h-4 bg-gray-200 rounded w-40"></div>
                                    </div>
                                </div>
                                <div class="border-2 border-gray-200 rounded-xl p-6 animate-pulse">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex-1">
                                            <div class="h-6 bg-gray-200 rounded w-32 mb-3"></div>
                                            <div class="h-5 bg-gray-200 rounded w-40 mb-2"></div>
                                            <div class="h-4 bg-gray-200 rounded w-24 mb-2"></div>
                                            <div class="h-4 bg-gray-200 rounded w-24"></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="h-7 bg-gray-200 rounded w-24 mb-3"></div>
                                            <div class="h-6 bg-gray-200 rounded w-20"></div>
                                        </div>
                                    </div>
                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="h-4 bg-gray-200 rounded w-40"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="orders-container" class="hidden space-y-4">
                                <!-- Orders will be loaded here via JavaScript -->
                            </div>
                            <div id="orders-empty" class="hidden text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p class="mt-4 text-gray-600 text-lg">No orders found.</p>
                                <p class="text-sm text-gray-500 mt-2">Start shopping to see your orders here.</p>
                            </div>
                        </div>
                    @elseif($activeSection === 'invoices')
                        <!-- Invoices Section -->
                        <div id="invoices-section">
                            <h2 class="text-3xl font-bold text-gray-900 mb-6">Invoices</h2>
                            <div class="space-y-4">
                                @forelse($invoices as $invoice)
                                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">{{ $invoice['invoice_number'] }}</h3>
                                                <p class="text-sm text-gray-600 mt-1">Order: {{ $invoice['order_id'] }}</p>
                                                <p class="text-sm text-gray-600">Issue Date: {{ $invoice['issue_date'] }}</p>
                                                <p class="text-sm text-gray-600">Due Date: {{ $invoice['due_date'] }}</p>
                                                <p class="text-sm text-gray-600">Payment Method: {{ $invoice['payment_method'] }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-bold text-purple-600 mb-2">US$ {{ number_format($invoice['amount'], 2) }}</p>
                                                <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold 
                                                    {{ $invoice['status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                                       ($invoice['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($invoice['status']) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-12">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="mt-4 text-gray-600 text-lg">No invoices found.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @elseif($activeSection === 'notifications')
                        <!-- Notifications Section -->
                        <div id="notifications-section">
                            <h2 class="text-3xl font-bold text-gray-900 mb-6">Notifications</h2>
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <p class="mt-4 text-gray-600 text-lg">No notifications found.</p>
                            </div>
                        </div>
                    @elseif($activeSection === 'account')
                        <!-- My Account Section -->
                        @auth
                        <div id="account-section">
                            <h2 class="text-3xl font-bold text-gray-900 mb-6">My Account</h2>
                            <div class="space-y-6 max-w-2xl">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                                    <input type="text" value="{{ $user['name'] }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" value="{{ $user['email'] }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" readonly>
                                </div>
                                @if(!empty($user['phone']))
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                    <input type="text" value="{{ $user['phone'] }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" readonly>
                                </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div id="account-section" class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">My Account</h2>
                            <p class="text-gray-600 mb-8">Please log in to view your account information.</p>
                            <div class="flex gap-4 justify-center">
                                <a href="{{ route('dashboard.myaccount') }}" class="px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                                    Login
                                </a>
                                <a href="{{ route('dashboard.myaccount') }}" class="px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                    Sign Up
                                </a>
                            </div>
                        </div>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($activeSection === 'orders')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ordersContainer = document.getElementById('orders-container');
    const ordersLoading = document.getElementById('orders-loading');
    const ordersEmpty = document.getElementById('orders-empty');
    
    // Get encrypted order IDs from localStorage
    const encryptedOrderIdsStr = localStorage.getItem('diaszone_encrypted_order_ids');
    
    if (!encryptedOrderIdsStr) {
        ordersLoading.classList.add('hidden');
        ordersEmpty.classList.remove('hidden');
        return;
    }
    
    let encryptedOrderIds = [];
    try {
        const parsed = JSON.parse(encryptedOrderIdsStr);
        encryptedOrderIds = Array.isArray(parsed) ? parsed : [parsed];
    } catch (e) {
        ordersLoading.classList.add('hidden');
        ordersEmpty.classList.remove('hidden');
        return;
    }
    
    if (encryptedOrderIds.length === 0) {
        ordersLoading.classList.add('hidden');
        ordersEmpty.classList.remove('hidden');
        return;
    }
    
    // Fetch all orders
    const fetchPromises = encryptedOrderIds.map((encryptedOrderId, index) => {
        return fetch('{{ route("api.orders.get-by-encrypted-id") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                encrypted_order_id: encryptedOrderId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.order) {
                return { order: data.order, encryptedId: encryptedOrderId };
            }
            return null;
        })
        .catch(error => {
            console.error('Error fetching order:', error);
            return null;
        });
    });
    
    Promise.all(fetchPromises).then(results => {
        ordersLoading.classList.add('hidden');
        
        const validResults = results.filter(result => result !== null);
        
        if (validResults.length === 0) {
            ordersEmpty.classList.remove('hidden');
            return;
        }
        
        ordersContainer.classList.remove('hidden');
        
        // Render orders
        ordersContainer.innerHTML = validResults.map(result => {
            const order = result.order;
            const encryptedOrderId = result.encryptedId;
            
            const statusColors = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'pending_flexy': 'bg-yellow-100 text-yellow-800',
                'pending_bmccp': 'bg-yellow-100 text-yellow-800',
                'pending_cryptopay': 'bg-yellow-100 text-yellow-800',
                'completed': 'bg-green-100 text-green-800',
                'sending': 'bg-blue-100 text-blue-800',
                'refunded': 'bg-red-100 text-red-800',
                'cancelled': 'bg-gray-100 text-gray-800'
            };
            
            const statusColor = statusColors[order.status] || 'bg-gray-100 text-gray-800';
            
            // Determine game type and currency
            // First try to get from diamond_pack, otherwise infer from order fields
            let gameType = order.diamond_pack?.game_type;
            
            // Fallback: determine game type from order fields if not in diamond_pack
            if (!gameType) {
                if (order.user_id_bs && order.server_bs) {
                    gameType = 'bloodstrike';
                } else if (order.player_id_ff) {
                    gameType = 'freefire';
                } else if (order.player_id_pubg) {
                    gameType = 'pubgmobile';
                } else if (order.player_id_hok) {
                    gameType = 'honorofkings';
                } else {
                    gameType = 'mobilelegends'; // Default
                }
            }
            
            let currencyText = 'Diamonds';
            let gameName = 'Mobile Legends';
            
            if (gameType === 'freefire') {
                currencyText = 'Diamonds';
                gameName = 'Free Fire';
            } else if (gameType === 'pubgmobile') {
                currencyText = 'UC';
                gameName = 'PUBG Mobile';
            } else if (gameType === 'honorofkings') {
                currencyText = 'Tokens';
                gameName = 'Honor of Kings';
            } else if (gameType === 'bloodstrike') {
                currencyText = 'Golds';
                gameName = 'Blood Strike';
            }
            
            // Determine pack display name
            let packDisplayName = '';
            if (order.diamond_pack?.name) {
                packDisplayName = order.diamond_pack.name;
            } else {
                packDisplayName = `${order.diamond_pack?.diamonds || 0} ${currencyText}`;
            }
            
            // Bonus display
            const bonus = order.diamond_pack?.bonus_diamonds || 0;
            const bonusText = bonus > 0 ? ` + ${bonus} Bonus ${currencyText}` : '';
            const packDisplayText = packDisplayName + bonusText;
            
            // Determine game type and display appropriate fields
            let gameInfo = '';
            if (gameType === 'bloodstrike') {
                // Blood Strike: User ID and Server
                const userIdBs = order.user_id_bs || '';
                const serverBs = order.server_bs || 'Global';
                gameInfo = `
                    <p class="text-sm text-gray-600 mb-1"><span class="font-medium">Game:</span> ${gameName}</p>
                    <p class="text-sm text-gray-600"><span class="font-medium">User ID:</span> ${userIdBs}</p>
                    <p class="text-sm text-gray-600"><span class="font-medium">Server:</span> ${serverBs}</p>
                `;
            } else if (gameType === 'freefire') {
                // Free Fire: Player ID
                const playerId = order.player_id_ff || '';
                gameInfo = `
                    <p class="text-sm text-gray-600 mb-1"><span class="font-medium">Game:</span> ${gameName}</p>
                    <p class="text-sm text-gray-600"><span class="font-medium">Player ID:</span> ${playerId}</p>
                `;
            } else if (gameType === 'pubgmobile') {
                // PUBG Mobile: Player ID
                const playerId = order.player_id_pubg || '';
                gameInfo = `
                    <p class="text-sm text-gray-600 mb-1"><span class="font-medium">Game:</span> ${gameName}</p>
                    <p class="text-sm text-gray-600"><span class="font-medium">Player ID:</span> ${playerId}</p>
                `;
            } else if (gameType === 'honorofkings') {
                // Honor of Kings: Player ID
                const playerId = order.player_id_hok || '';
                gameInfo = `
                    <p class="text-sm text-gray-600 mb-1"><span class="font-medium">Game:</span> ${gameName}</p>
                    <p class="text-sm text-gray-600"><span class="font-medium">Player ID:</span> ${playerId}</p>
                `;
            } else {
                // Mobile Legends (default): User ID and Zone ID
                const userId = order.user_id_ml || '';
                const zoneId = order.zone_id_ml || '';
                gameInfo = `
                    <p class="text-sm text-gray-600 mb-1"><span class="font-medium">Game:</span> ${gameName}</p>
                    <p class="text-sm text-gray-600"><span class="font-medium">User ID:</span> ${userId}</p>
                    <p class="text-sm text-gray-600"><span class="font-medium">Zone ID:</span> ${zoneId}</p>
                `;
            }
            
            // Continue Payment button (show based on pending status)
            let continuePaymentBtn = '';
            let paymentUrl = '';
            
            if (order.status === 'pending_flexy') {
                paymentUrl = `/select/flexy?order_id=${encodeURIComponent(encryptedOrderId)}`;
            } else if (order.status === 'pending_bmccp') {
                paymentUrl = `/select/bmccp/${encodeURIComponent(encryptedOrderId)}`;
            } else if (order.status === 'pending_cryptopay') {
                paymentUrl = `/select/crypto/${encodeURIComponent(encryptedOrderId)}`;
            }
            
            if (paymentUrl) {
                continuePaymentBtn = `
                    <a href="${paymentUrl}" 
                       class="mt-4 inline-block px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                        Continue Payment
                    </a>
                `;
            }
            
            return `
                <div class="border-2 border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Order #${order.order_number}</h3>
                            <p class="text-lg text-purple-600 font-semibold mb-2">${packDisplayText}</p>
                            ${gameInfo}
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-purple-600 mb-2">US$ ${order.amount.toFixed(2)}</p>
                            <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold ${statusColor}">
                                ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                            </span>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 border-t border-gray-200 pt-4">
                        <p>Created: ${new Date(order.created_at).toLocaleString()}</p>
                    </div>
                    ${continuePaymentBtn}
                </div>
            `;
        }).join('');
    });
});
</script>
@endpush
@endif

@push('scripts')
<script>
// Sticky sidebar functionality (similar to Terms of Use and Privacy Policy)
(function() {
    const sidebar = document.getElementById('dashboard-sidebar');
    const sidebarContent = sidebar?.querySelector('div');
    const sidebarSpacer = document.getElementById('sidebar-spacer');
    
    if (!sidebar || !sidebarContent) return;
    
    // Only apply on desktop
    if (window.innerWidth < 1024) return;
    
    let sidebarTop = 0;
    let sidebarWidth = 0;
    let sidebarLeft = 0;
    let scrollTimeout = null;
    
    function initSidebar() {
        if (window.innerWidth < 1024) {
            sidebar.style.position = '';
            sidebar.style.top = '';
            sidebar.style.left = '';
            sidebar.style.width = '';
            sidebar.style.zIndex = '';
            sidebarContent.style.maxHeight = '';
            sidebarContent.style.overflowY = '';
            if (sidebarSpacer) {
                sidebarSpacer.style.display = 'none';
            }
            return;
        }
        
        const rect = sidebar.getBoundingClientRect();
        sidebarTop = rect.top + window.pageYOffset;
        sidebarWidth = rect.width;
        sidebarLeft = rect.left;
    }
    
    function updateSidebarPosition() {
        if (window.innerWidth < 1024) {
            sidebar.style.position = '';
            sidebar.style.top = '';
            sidebar.style.left = '';
            sidebar.style.width = '';
            sidebar.style.zIndex = '';
            sidebarContent.style.maxHeight = '';
            sidebarContent.style.overflowY = '';
            if (sidebarSpacer) {
                sidebarSpacer.style.display = 'none';
            }
            return;
        }
        
        if (sidebarTop === 0) {
            initSidebar();
        }
        
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const headerHeight = 64; // Header height
        const headerOffset = headerHeight + 20; // Add some padding
        
        // Calculate the maximum scroll position where sidebar should be sticky
        const contentEnd = document.querySelector('.container').offsetHeight;
        const windowHeight = window.innerHeight;
        const footerHeight = 0; // Footer is hidden on dashboard
        const maxStickyScroll = contentEnd - windowHeight - footerHeight;
        
        const shouldBeSticky = scrollTop >= sidebarTop - headerOffset && scrollTop <= maxStickyScroll;
        
        if (shouldBeSticky) {
            sidebar.style.position = 'fixed';
            sidebar.style.top = headerOffset + 'px';
            sidebar.style.left = sidebarLeft + 'px';
            sidebar.style.width = sidebarWidth + 'px';
            sidebar.style.zIndex = '40';
            
            if (sidebarSpacer) {
                sidebarSpacer.style.display = 'block';
            }
            
            const availableHeight = windowHeight - headerOffset - 40;
            sidebarContent.style.maxHeight = availableHeight + 'px';
            sidebarContent.style.overflowY = 'auto';
        } else {
            sidebar.style.position = '';
            sidebar.style.top = '';
            sidebar.style.left = '';
            sidebar.style.width = '';
            sidebar.style.zIndex = '';
            sidebarContent.style.maxHeight = '';
            sidebarContent.style.overflowY = '';
            
            if (sidebarSpacer) {
                sidebarSpacer.style.display = 'none';
            }
            
            if (scrollTop < sidebarTop - headerOffset) {
                const availableHeight = windowHeight - headerOffset - 40;
                sidebarContent.style.maxHeight = availableHeight + 'px';
            }
        }
        
        // Update left position if needed (for responsive changes)
        const rect = sidebar.getBoundingClientRect();
        if (rect.left !== sidebarLeft) {
            sidebarLeft = rect.left;
            sidebar.style.left = sidebarLeft + 'px';
        }
    }
    
    // Throttle scroll events
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(updateSidebarPosition, 10);
    });
    
    window.addEventListener('resize', function() {
        initSidebar();
        updateSidebarPosition();
    });
    
    // Initialize on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initSidebar();
            updateSidebarPosition();
        });
    } else {
        initSidebar();
        updateSidebarPosition();
    }
})();
</script>
@endpush
@endsection
