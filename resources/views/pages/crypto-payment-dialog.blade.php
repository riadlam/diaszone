<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Crypto Payment - DiasZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- QR code will be generated via API (no JavaScript library needed) -->
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .skeleton {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <div class="bg-white/10 backdrop-blur-lg border-b border-white/20">
            <div class="container mx-auto px-4 py-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button onclick="window.history.back()" class="text-white hover:bg-white/10 rounded-full p-1.5 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-lg font-bold text-white">{{ __('checkout.pay_with') }} {{ __('payment.crypto') }}</h1>
                            <p class="text-white/70 text-xs">{{ __('payment.scan_qr_code_to_pay') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 container mx-auto px-4 py-3 max-w-md overflow-y-auto">
            <!-- Loading Skeleton -->
            <div id="loading-skeleton" class="space-y-4">
                <div class="text-center">
                    <div class="skeleton bg-white/10 rounded-2xl w-full aspect-square max-w-xs mx-auto mb-3"></div>
                    <div class="skeleton bg-white/10 rounded-lg h-5 w-40 mx-auto mb-1"></div>
                    <div class="skeleton bg-white/10 rounded-lg h-3 w-28 mx-auto"></div>
                </div>
                <div class="space-y-2">
                    <div class="skeleton bg-white/10 rounded-xl h-12"></div>
                    <div class="skeleton bg-white/10 rounded-xl h-12"></div>
                </div>
            </div>

            <!-- Payment Content -->
            <div id="payment-content" style="display: none;">
                <!-- Amount Display -->
                <div class="text-center mb-4">
                    <p class="text-white/60 text-xs mb-1">Amount to Pay</p>
                    <div class="flex items-baseline justify-center gap-2 mb-1">
                        <span class="text-3xl font-bold text-white" id="pay-amount">0</span>
                        <span class="text-lg text-white/80" id="pay-currency">USDT</span>
                    </div>
                    <p class="text-white/50 text-sm">
                        ≈ <span id="price-amount">$0</span> <span id="price-currency">USD</span>
                    </p>
                </div>

                <!-- Pay Button Card -->
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-2xl p-6 mb-4 shadow-2xl">
                    <div class="text-center">
                        <button id="pay-now-button" onclick="openPaymentPage()" class="w-full bg-white hover:bg-gray-100 text-purple-600 font-bold py-4 px-6 rounded-xl transition-all shadow-lg text-lg">
                            {{ __('payment.pay_with_nowpayments') }}
                        </button>
                        <p class="text-white/80 text-xs mt-3">{{ __('payment.opens_nowpayments_info') }}</p>
                        <div class="mt-3 bg-white/10 rounded-lg p-2">
                            <p class="text-white/60 text-[10px] mb-1 font-medium">Payment URL:</p>
                            <code id="payment-url-display" class="text-white text-[10px] font-mono break-all block text-left"></code>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="space-y-2 mb-3">
                    <div class="bg-white/10 backdrop-blur-lg rounded-xl p-3 border border-white/20">
                        <p class="text-white/60 text-xs mb-1 font-medium">Payment Address</p>
                        <div class="flex items-center gap-2">
                            <code class="text-xs font-mono text-white flex-1 break-all" id="pay-address"></code>
                                <button onclick="copyToClipboard('pay-address')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors flex-shrink-0 shadow-lg">
                                    {{ __('common.copy') }}
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-white/10 backdrop-blur-lg rounded-xl p-3 border border-white/20">
                            <p class="text-white/60 text-xs mb-1 font-medium">{{ __('payment.network') }}</p>
                            <p class="text-white text-sm font-semibold" id="network">TRC20</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-lg rounded-xl p-3 border border-white/20">
                            <p class="text-white/60 text-xs mb-1 font-medium">{{ __('payment.payment_id') }}</p>
                            <button onclick="copyToClipboard('payment-id')" class="text-white/80 text-xs font-mono break-all w-full text-left" id="payment-id"></button>
                        </div>
                    </div>
                </div>

                <!-- Payment Status -->
                <div class="bg-blue-500/20 backdrop-blur-lg border border-blue-400/30 rounded-xl p-3 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
                        <div class="flex-1">
                            <p class="text-white text-xs font-semibold">
                                Status: <span id="payment-status-text">{{ __('payment.waiting_for_payment') }}</span>
                            </p>
                            <p class="text-white/60 text-xs mt-0.5">Status updates automatically via webhook</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <button onclick="checkPaymentStatus()" class="w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-xl">
                        {{ __('payment.check_status') }}
                    </button>
                    <button onclick="openInWallet()" class="w-full bg-white/10 hover:bg-white/20 backdrop-blur-lg text-white font-semibold py-3 px-6 rounded-xl transition-all border border-white/20">
                        {{ __('payment.open_in_wallet') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let paymentData = null;
        let statusCheckInterval = null;
        let paymentUrl = null;

        // Auto-load payment data on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch payment data from API (no need to wait for QRCode library)
            fetch('/test/nowpayments/payment', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadPaymentData(data);
                } else {
                    alert({!! json_encode(__('seller.failed_to_create_payment_prefix')) !!} + (data.error || {!! json_encode(__('seller.unknown_error')) !!}));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert({!! json_encode(__('seller.failed_to_load_payment_data')) !!});
            });
        });

        function loadPaymentData(data) {
            paymentData = data;
            
            // Hide skeleton, show content
            document.getElementById('loading-skeleton').style.display = 'none';
            document.getElementById('payment-content').style.display = 'block';

            // Set payment details
            const payAmount = parseFloat(data.pay_amount || data.payment?.pay_amount || 0);
            const priceAmount = parseFloat(data.price_amount || data.payment?.price_amount || 0);
            const payCurrency = data.pay_currency || data.payment?.pay_currency || 'usdttrc20';
            const priceCurrency = data.price_currency || data.payment?.price_currency || 'USD';
            const payAddress = data.pay_address || data.payment?.pay_address || '';
            const paymentId = data.payment_id || data.payment?.payment_id || '';

            document.getElementById('pay-amount').textContent = payAmount.toFixed(6);
            document.getElementById('pay-currency').textContent = getCurrencyName(payCurrency);
            document.getElementById('price-amount').textContent = priceAmount.toFixed(2);
            document.getElementById('price-currency').textContent = priceCurrency;
            document.getElementById('pay-address').textContent = payAddress;
            document.getElementById('payment-id').textContent = paymentId;
            
            // Set network based on currency
            const currency = payCurrency.toLowerCase();
            let network = 'TRC20';
            if (currency.includes('erc20')) network = 'ERC20';
            else if (currency.includes('bsc')) network = 'BSC';
            else if (currency.includes('matic')) network = 'Polygon';
            else if (currency.includes('sol')) network = 'Solana';
            document.getElementById('network').textContent = network;

            // Store payment URL for the button
            paymentUrl = data.payment_url || data.payment?.payment_url || data.invoice_url || data.payment?.invoice_url || null;
            
            // Display payment URL
            const urlDisplay = document.getElementById('payment-url-display');
            if (paymentUrl && paymentUrl !== '#' && paymentUrl !== null) {
                urlDisplay.textContent = paymentUrl;
            } else {
                urlDisplay.textContent = 'Not available';
                urlDisplay.classList.add('text-red-300');
            }
            
            // Enable/disable pay button based on payment URL availability
            const payButton = document.getElementById('pay-now-button');
            if (paymentUrl && paymentUrl !== '#' && paymentUrl !== null) {
                payButton.disabled = false;
                payButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                payButton.disabled = true;
                payButton.classList.add('opacity-50', 'cursor-not-allowed');
                payButton.textContent = {!! json_encode(__('payment.url_not_available')) !!};
            }
            
            // Start status checking
            startStatusCheck();
        }

        function openPaymentPage() {
            if (!paymentUrl || paymentUrl === '#' || paymentUrl === null) {
                alert({!! json_encode(__('seller.payment_url_not_available')) !!});
                return;
            }
            
            // Open NOWPayments payment page in a new tab/window
            // This page will handle opening the wallet app (Binance, etc.)
            window.open(paymentUrl, '_blank');
        }

        function getCurrencyName(currency) {
            const currencyMap = {
                'usdttrc20': 'USDT',
                'usdterc20': 'USDT',
                'usdtbsc': 'USDT',
                'usdtmatic': 'USDT',
                'usdtsol': 'USDT',
                'btc': 'BTC',
                'eth': 'ETH',
                'bnb': 'BNB',
            };
            return currencyMap[currency.toLowerCase()] || currency.toUpperCase();
        }

        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            
            navigator.clipboard.writeText(text).then(() => {
                // Show success feedback
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.classList.add('bg-green-600');
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('bg-green-600');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                alert({!! json_encode(__('seller.clipboard_copy_failed')) !!});
            });
        }

        function checkPaymentStatus() {
            if (!paymentData) return;
            
            const paymentId = paymentData.payment_id || paymentData.payment?.payment_id;
            if (!paymentId) return;

            const statusText = document.getElementById('payment-status-text');
            statusText.textContent = 'Checking...';

            fetch(`/test/nowpayments/status/${paymentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const status = data.payment_status || data.payment_data?.payment_status;
                        statusText.textContent = getStatusText(status);
                        
                        if (status === 'finished' || status === 'confirmed') {
                            statusText.textContent = 'Payment Confirmed!';
                            const statusDiv = statusText.parentElement.parentElement;
                            statusDiv.classList.remove('bg-blue-500/20', 'border-blue-400/30');
                            statusDiv.classList.add('bg-green-500/20', 'border-green-400/30');
                            statusText.classList.remove('text-white');
                            statusText.classList.add('text-green-300');
                            
                            setTimeout(() => {
                                alert({!! json_encode(__('seller.payment_confirmed_redirecting')) !!});
                                window.location.href = '/dashboard/orders';
                            }, 2000);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error checking status:', error);
                    statusText.textContent = 'Error checking status';
                });
        }

        function getStatusText(status) {
            const statusMap = {
                'waiting': 'Waiting for payment...',
                'confirming': 'Confirming payment...',
                'confirmed': 'Payment confirmed',
                'finished': 'Payment completed',
                'failed': 'Payment failed',
                'expired': 'Payment expired'
            };
            return statusMap[status] || status;
        }

        function startStatusCheck() {
            // No automatic polling - we rely on IPN webhook for instant updates
            // Webhook will automatically update order status when payment is detected
            console.log('Payment status will be updated via IPN webhook when payment is received');
        }

        function openInWallet() {
            // Use NOWPayments payment URL (same as main pay button)
            // This opens the NOWPayments page which handles wallet app opening
            if (paymentUrl && paymentUrl !== '#' && paymentUrl !== null) {
                window.open(paymentUrl, '_blank');
            } else {
                alert({!! json_encode(__('seller.payment_url_not_available')) !!});
            }
        }
    </script>
</body>
</html>
