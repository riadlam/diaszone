@extends('layouts.app')

@section('title', $gameTitle . ' - DiasZone')

@section('content')
<!-- Game Header -->
@include('components.game-header', ['gameType' => $gameType, 'gameTitle' => $gameTitle])

<!-- Main Content: Offers and Order Form -->
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen">
    <div class="container mx-auto px-4" style="padding-top: 1rem !important; padding-bottom: 2rem !important;" id="main-container">
        <div id="offers-section" class="flex flex-col lg:flex-row">
            <!-- Left Column: Diamond Packs (Scrollable) - Hidden on mobile -->
            <div class="flex-1 hidden lg:block" style="margin-right: 15px !important;">
                @include('components.diamond-packs', ['packs' => $packs, 'gameType' => $gameType, 'gameTitle' => $gameTitle])
            </div>
            
            <!-- Right Column: Order Form (Sticky on desktop, full width on mobile) -->
            <div id="order-form-wrapper" class="w-full lg:w-96 lg:mt-0" data-game-type="{{ $gameType }}">
                <!-- Mobile: Select Pack Button (moved here to be in same column) -->
                <div class="lg:hidden mb-4" id="mobile-select-pack-container">
                    <button id="mobile-select-pack-btn" 
                            type="button"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-4 px-6 rounded-lg transition-colors shadow-md hover:shadow-lg flex items-center justify-between">
                        <span id="mobile-selected-pack-text" class="text-left">
                            <span class="block text-sm font-medium">{{ __('home.select_topup_amount') }}</span>
                            <span id="mobile-selected-pack-details" class="text-xs opacity-75 hidden"></span>
                        </span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                
                @include('components.order-form', ['gameTitle' => $gameTitle, 'gameType' => $gameType])
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Sheet - Always accessible, outside hidden column -->
@include('components.mobile-bottom-sheet', ['packs' => $packs, 'gameType' => $gameType, 'gameTitle' => $gameTitle])

<!-- Recharge Info Section -->
@include('components.recharge-info', ['gameType' => $gameType, 'gameTitle' => $gameTitle])
@endsection

