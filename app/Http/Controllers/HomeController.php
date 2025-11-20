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

    public function about()
    {
        return view('pages.about');
    }

    public function termsOfUse()
    {
        return view('pages.terms-of-use');
    }

    public function privacyPolicy()
    {
        return view('pages.privacy-policy');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // Here you can add logic to send email, save to database, etc.
        // For now, we'll just return a success response
        
        // TODO: Implement email sending or database storage
        // Example: Mail::to('support@diaszone.com')->send(new ContactFormMail($request->all()));
        
        return response()->json([
            'success' => true,
            'message' => 'Thank you for contacting us! We have received your message and will get back to you soon.'
        ]);
    }
}
