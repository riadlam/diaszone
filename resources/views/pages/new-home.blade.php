@extends('layouts.app')

@section('title', __('home.page_title'))

@section('content')
<style>
    body {
        background-color: #ffffff !important;
    }
</style>
@include('components.hero-slider', [
    'heroSlides' => $heroSlides ?? collect(),
])

@include('components.flash-sales', [
    'flashSales' => $flashSales ?? collect(),
    'flashSaleEndsAt' => $flashSaleEndsAt ?? null,
])

<!-- Home Services Section -->
@include('components.home-services', ['games' => $games])

<!-- Top Selling Products Section -->
@include('components.top-selling-products', ['topSellingGames' => $topSellingGames ?? []])

<!-- Banner Section -->
@include('components.banner-section')

<!-- New Products Section -->
@include('components.new-products', ['newProducts' => $newProducts ?? []])

<!-- Banner Two Images Section -->
@include('components.banner-two-images')

<!-- Gift Cards Section -->
@include('components.gift-cards', ['giftCards' => $giftCards ?? []])

<!-- Recharge Info Section -->
@include('components.recharge-info')
@endsection
