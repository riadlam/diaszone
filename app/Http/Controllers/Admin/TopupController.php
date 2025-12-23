<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletRechargeAsk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopupController extends Controller
{
    public function index()
    {
        $requests = WalletRechargeAsk::with('seller')->latest()->paginate(30);
        return view('admin.topups.index', compact('requests'));
    }

    public function approve(Request $request, WalletRechargeAsk $topup)
    {
        if ($topup->status !== 'pending') {
            return back()->withErrors(['topup' => 'Top-up is not pending']);
        }

        DB::beginTransaction();
        try {
            $seller = $topup->seller;

            // Credit the seller's wallet and create a transaction
            $seller->creditWallet($topup->amount, 'Top-up approved #' . $topup->id, auth()->id(), $topup->id, 'topup');

            // Try to link the most recent wallet transaction as the reference
            $tx = $seller->walletTransactions()->latest()->first();
            if ($tx) {
                $topup->transaction_id = $tx->id;
            }

            $topup->status = 'approved';
            $topup->admin_note = $request->input('admin_note');
            $topup->admin_id = auth()->id();
            $topup->processed_at = now();
            $topup->save();

            DB::commit();
            return back()->with('success', 'Top-up approved and funds added to seller wallet.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Could not approve top-up: ' . $e->getMessage()]);
        }
    }

    public function reject(Request $request, WalletRechargeAsk $topup)
    {
        if ($topup->status !== 'pending') {
            return back()->withErrors(['topup' => 'Top-up is not pending']);
        }

        $topup->status = 'rejected';
        $topup->admin_note = $request->input('admin_note');
        $topup->admin_id = auth()->id();
        $topup->processed_at = now();
        $topup->save();

        return back()->with('success', 'Top-up request rejected.');
    }
}
