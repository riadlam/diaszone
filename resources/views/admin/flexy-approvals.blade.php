@extends('layouts.admin')

@section('title', 'Flexy Approvals - Admin')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Flexy Transfer Approvals</h1>
            <p class="text-gray-600 mt-2 text-sm md:text-base">Review pending Flexy transfers and approve or reject them.</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-4 py-3 text-sm font-medium text-gray-600">Order</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-600">Seller</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-600">Pack</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-600">Amount</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-600">Receipt</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-600">Notes</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-600">Date</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($orders as $order)
                            <tr>
                                <td class="px-4 py-4 font-mono text-sm">{{ $order->order_number }}</td>
                                <td class="px-4 py-4 text-sm">{{ $order->seller->store_name ?? $order->seller->name }}</td>
                                <td class="px-4 py-4 text-sm">{{ $order->diamondPack->name ?? 'N/A' }}</td>
                                <td class="px-4 py-4 text-sm">{{ number_format($order->final_price ?? 0, 2) }} DZD</td>
                                <td class="px-4 py-4 text-sm">
                                    @if($order->flexy_receipt)
                                        @php
                                            $ext = pathinfo(storage_path('app/public/' . $order->flexy_receipt), PATHINFO_EXTENSION);
                                        @endphp
                                        @if(in_array(strtolower($ext), ['png','jpg','jpeg','webp']))
                                            <a href="{{ asset('storage/' . $order->flexy_receipt) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $order->flexy_receipt) }}" alt="receipt" class="w-20 h-12 object-cover rounded" />
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/' . $order->flexy_receipt) }}" target="_blank" class="text-blue-600 underline">Download</a>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">{{ $order->flexy_description ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-gray-500">{{ $order->created_at->format('M d, H:i') }}</td>
                                <td class="px-4 py-4 text-sm space-x-2">
                                    <form method="POST" action="{{ route('admin.flexy.approvals.approve', ['orderNumber' => $order->order_number]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.flexy.approvals.reject', ['orderNumber' => $order->order_number]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">No pending flexy approvals</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
