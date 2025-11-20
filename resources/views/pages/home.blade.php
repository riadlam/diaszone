@extends('layouts.app')

@section('title', 'Home - DiasZone')

@section('content')
<!-- Hero Slider -->
@include('components.hero-slider')

<!-- Main Content: Offers and Order Form -->
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div id="offers-section" class="flex flex-col lg:flex-row gap-6" style="overflow: visible !important; position: relative !important;">
            <!-- Left Column: Diamond Packs (Scrollable) -->
            <div class="flex-1" style="overflow: visible !important;">
                @include('components.diamond-packs', ['packs' => $packs])
            </div>
            
            <!-- Right Column: Order Form (Sticky) -->
            <div id="order-form-wrapper" class="lg:w-96" style="position: sticky !important; top: 80px !important; align-self: flex-start !important; max-height: calc(100vh - 100px) !important; z-index: 10 !important; height: fit-content !important;">
                @include('components.order-form')
            </div>
        </div>
    </div>
</div>

<!-- Recharge Info Section -->
@include('components.recharge-info')
@endsection


