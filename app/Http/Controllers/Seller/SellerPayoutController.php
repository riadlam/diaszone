<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerPayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerPayoutController extends Controller
{
    public function store(Request $request)
    {
        $seller = Auth::guard('seller')->user();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'seller_note' => 'nullable|string|max:2000',
        ]);

        $amount = round($validated['amount'], 2);

        // Ensure seller has enough balance
        if ($seller->wallet_balance < $amount) {
            return back()->withErrors(['amount' => 'Insufficient wallet balance for this payout request.']);
        }

        // Create request record
        $payout = SellerPayoutRequest::create([
            'seller_id' => $seller->id,
            'amount' => $amount,
            'currency' => 'DZD',
            'status' => 'pending',
            'seller_note' => $validated['seller_note'] ?? null,
        ]);

        // Reserve funds: deduct wallet and link transaction
        $tx = $seller->deductWallet($amount, 'Payout request #' . $payout->id, $payout->id, 'payout_request');
        if (!$tx) {
            $payout->delete();
            return back()->withErrors(['amount' => 'Failed to reserve funds for payout.']);
        }

        $payout->transaction_id = $tx->id;
        $payout->save();

        return back()->with('success', 'Payout request submitted and pending admin approval.');
    }
}
