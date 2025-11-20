<?php

namespace App\Http\Controllers;

use App\Models\DiamondPack;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $packs = DiamondPack::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('pages.home', compact('packs'));
    }
}
