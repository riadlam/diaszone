<div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 lg:p-6" style="padding: 20px;">
    <h2 class="text-lg font-semibold text-gray-800 mb-3 lg:mb-4">Order Information</h2>
    
    <form id="order-form" class="space-y-4">
        <!-- User ID -->
        <div>
            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">User ID</label>
            <input type="text" 
                   id="user_id" 
                   name="user_id" 
                   required
                   pattern="[0-9]+"
                   class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm"
                   placeholder="Enter your User ID">
        </div>
        
        <!-- Zone ID -->
        <div>
            <label for="zone_id" class="block text-sm font-medium text-gray-700 mb-2">Zone ID</label>
            <input type="text" 
                   id="zone_id" 
                   name="zone_id" 
                   required
                   pattern="[0-9]+"
                   class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm"
                   placeholder="Enter your Zone ID">
        </div>
        
        <!-- Selected Pack Info -->
        <div id="selected-pack-info" class="hidden bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-gray-700">Selected Pack:</span>
                <span id="pack-name" class="text-sm font-bold text-purple-600"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-600">Price:</span>
                <span id="pack-price" class="text-sm font-semibold text-purple-600"></span>
            </div>
        </div>
        
        <!-- Total Price -->
        <div class="pt-4 border-t border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-base font-semibold text-gray-900">Total:</span>
                <span id="total-price" class="text-lg font-bold text-purple-600">US$ 0.00</span>
            </div>
            <p id="diaszone-credit" class="text-xs text-gray-500 lowercase text-right">diaszone credit 0</p>
        </div>
        
        <!-- Buy Now Button -->
        <button type="submit" 
                id="buy-now-btn"
                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors shadow-md hover:shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed">
            Buy Now
        </button>
    </form>
</div>


