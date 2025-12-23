<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\WalletRechargeAsk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SellerTopupController extends Controller
{
    public function store(Request $request)
    {
        $seller = Auth::guard('seller')->user();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'seller_note' => 'nullable|string|max:2000',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'payment_type' => 'required|string|in:ccp,crypto,baridimob',
            // Phone is required for baridimob payments (algerie poste)
            'phone' => 'required_if:payment_type,baridimob|string|max:32',
        ]);

        $amount = round($validated['amount'], 2);

        // determine currency: crypto top-ups are handled in USD; other types use local DZD
        $currency = ($validated['payment_type'] ?? '') === 'crypto' ? 'USD' : 'DZD';

        $payload = [
            'seller_id' => $seller->id,
            'amount' => $amount,
            'currency' => $currency,
            'payment_type' => $validated['payment_type'] ?? null,
            'status' => 'pending',
            'seller_note' => $validated['seller_note'] ?? null,
        ];

        // Save receipt file if provided
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('recharge_receipts', 'public');
            // Store a public URL so views can render it directly
            $payload['receipt'] = Storage::url($path);
        }

        $topup = WalletRechargeAsk::create($payload);

        return back()->with('success', 'Top-up request submitted and pending admin approval.');
    }
}
