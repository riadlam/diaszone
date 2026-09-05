@extends('layouts.app')

@section('title', 'Digital')

@section('content')
<style>
    body {
        background-color: #ffffff !important;
    }
</style>

@include('components.hero-slider', [
    'heroSlides' => $heroSlides ?? collect(),
])

@include('components.digital-products', [
    'categories' => $categories ?? [],
])
@endsection
