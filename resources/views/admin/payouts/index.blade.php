@extends('layouts.admin')

@section('title', 'Payout Requests - Admin')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Payout Requests</h1>

    <div class="bg-white rounded-xl p-4 shadow">
        <table class="w-full table-auto">
            <thead>
                <tr class="text-left text-sm text-gray-600 border-b">
                    <th class="py-2 px-4">ID</th>
                    <th class="py-2 px-4">Seller</th>
                    <th class="py-2 px-4">Amount</th>
                    <th class="py-2 px-4">Status</th>
                    <th class="py-2 px-4">Requested At</th>
                    <th class="py-2 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $r)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2 px-4">#{{ $r->id }}</td>
                    <td class="py-2 px-4">{{ $r->seller->name }} ({{ $r->seller->username }})</td>
                    <td class="py-2 px-4">{{ number_format($r->amount, 0, '.', '') }} {{ $r->currency }}</td>
                    <td class="py-2 px-4">{{ ucfirst($r->status) }}</td>
                    <td class="py-2 px-4">{{ $r->created_at->format('M d, Y H:i') }}</td>
                    <td class="py-2 px-4">
                        @if($r->status === 'pending')
                        <form method="POST" action="{{ route('admin.payouts.approve', ['payout' => $r->id]) }}" class="inline-block mr-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="admin_note" value="Approved by admin">
                            <button class="px-3 py-1 bg-green-600 text-white rounded">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.payouts.reject', ['payout' => $r->id]) }}" class="inline-block">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="admin_note" value="Rejected by admin">
                            <button class="px-3 py-1 bg-red-600 text-white rounded">Reject</button>
                        </form>
                        @else
                            <span class="text-sm text-gray-500">{{ $r->admin_note }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
