@extends('layouts.app')

@section('title', 'Home - DiasZone')

@section('content')
<!-- Hero Slider -->
@include('components.hero-slider')

<!-- Game Top Up Section -->
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen py-12">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Game Top Up</h2>
            <p class="text-gray-600 text-lg">Choose your favorite game and top up instantly</p>
        </div>
        
        <!-- Games Grid -->
        <style>
            .game-card {
                width: calc(50% - 6px);
                max-width: calc(50% - 6px);
            }
            @media (min-width: 768px) {
                .game-card {
                    width: calc(33.333% - 11px);
                    max-width: calc(33.333% - 11px);
                }
            }
            @media (min-width: 1024px) {
                .game-card {
                    width: calc(20% - 13px);
                    max-width: calc(20% - 13px);
                }
            }
        </style>
        <div class="flex flex-wrap justify-center gap-3 md:gap-4 lg:gap-4 max-w-7xl mx-auto">
            <!-- Mobile Legends -->
            <a href="{{ route('mobilelegends') }}" class="group game-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-purple-500">
                <div class="aspect-square relative overflow-hidden">
                    <img src="{{ asset('storage/images_homepage/mobilelegends.webp') }}" 
                         alt="Mobile Legends" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                        <h3 class="text-white font-bold text-lg">Mobile Legends</h3>
                    </div>
                </div>
            </a>
            
            <!-- Free Fire -->
            <a href="{{ route('freefire') }}" class="group game-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-purple-500">
                <div class="aspect-square relative overflow-hidden">
                    <img src="{{ asset('storage/images_homepage/freefire.webp') }}" 
                         alt="Free Fire" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                        <h3 class="text-white font-bold text-lg">Free Fire</h3>
                    </div>
                </div>
            </a>
            
            <!-- Honor of Kings -->
            <a href="{{ route('honorofkings') }}" class="group game-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-purple-500">
                <div class="aspect-square relative overflow-hidden">
                    <img src="{{ asset('storage/images_homepage/honorofkings.webp') }}" 
                         alt="Honor of Kings" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                        <h3 class="text-white font-bold text-lg">Honor of Kings</h3>
                    </div>
                </div>
            </a>
            
            <!-- Blood Strike -->
            <a href="{{ route('bloodstrike') }}" class="group game-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-purple-500">
                <div class="aspect-square relative overflow-hidden">
                    <img src="{{ url('storage/images_homepage/bloodrivels.webp') }}" 
                         alt="Blood Strike" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                        <h3 class="text-white font-bold text-lg">Blood Strike</h3>
                    </div>
                </div>
            </a>
            
            <!-- PUBG Mobile -->
            <a href="{{ route('pubgmobile') }}" class="group game-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-purple-500">
                <div class="aspect-square relative overflow-hidden">
                    <img src="{{ asset('storage/images_homepage/pubgmobile.webp') }}" 
                         alt="PUBG Mobile" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                        <h3 class="text-white font-bold text-lg">PUBG Mobile</h3>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Recharge Info Section -->
@include('components.recharge-info')
@endsection

