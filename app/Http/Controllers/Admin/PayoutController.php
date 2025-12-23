<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerPayoutRequest;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    public function index()
    {
        $requests = SellerPayoutRequest::with('seller')->latest()->paginate(30);
        return view('admin.payouts.index', compact('requests'));
    }

    public function approve(Request $request, SellerPayoutRequest $payout)
    {
        if ($payout->status !== 'pending') {
            return back()->withErrors(['payout' => 'Payout is not pending']);
        }

        $payout->status = 'approved';
        $payout->admin_note = $request->input('admin_note');
        $payout->processed_at = now();
        $payout->save();

        // Optionally, record admin payment reference (external payouts are off-site)
        return back()->with('success', 'Payout approved.');
    }

    public function reject(Request $request, SellerPayoutRequest $payout)
    {
        if ($payout->status !== 'pending') {
            return back()->withErrors(['payout' => 'Payout is not pending']);
        }

        DB::beginTransaction();
        try {
            // Refund the reserved funds by crediting the seller wallet
            $seller = $payout->seller;
            $seller->creditWallet($payout->amount, 'Payout rejected #' . $payout->id, auth()->id(), $payout->id, 'payout_refund');

            $payout->status = 'rejected';
            $payout->admin_note = $request->input('admin_note');
            $payout->processed_at = now();
            $payout->save();

            DB::commit();
            return back()->with('success', 'Payout request rejected and funds returned to seller wallet.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Could not reject payout: ' . $e->getMessage()]);
        }
    }
}
