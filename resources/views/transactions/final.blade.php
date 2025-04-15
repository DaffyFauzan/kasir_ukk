<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-lg font-semibold mb-4">Transaction Summary</h2>

                    <div class="mb-6">
                        <h3 class="font-medium text-gray-700 mb-2">Customer Information</h3>
                        <p><strong>Name:</strong> {{ $transaction->customer->name ?? 'Non-Member' }}</p>
                        <p><strong>Phone:</strong> {{ $transaction->customer->no_telp ?? 'N/A' }}</p>
                        <p><strong>Points Earned:</strong> {{ $transaction->poin }}</p>
                        <p><strong>Total Points:</strong> {{ $transaction->customer->poin ?? 0 }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="font-medium text-gray-700 mb-2">Products Purchased</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($transaction->detailSales as $detail)
                                    <tr>
                                        <td class="px-6 py-4">{{ $detail->product->name }}</td>
                                        <td class="px-6 py-4">{{ $detail->amount }}</td>
                                        <td class="px-6 py-4">Rp {{ number_format($detail->product->price, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-6">
                        <h3 class="font-medium text-gray-700 mb-2">Points Information</h3>
                        <p><strong>Previous Points:</strong> {{ $transaction->customer->poin - $transaction->poin }}</p>
                        <p><strong>Points Earned This Transaction:</strong> {{ $transaction->poin }}</p>
                        <p><strong>Total Points After Transaction:</strong> {{ $transaction->customer->poin }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="font-medium text-gray-700 mb-2">Payment Details</h3>
                        <p><strong>Total Price:</strong> Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</p>
                        @if($discount > 0)
                            <p><strong>Points Discount:</strong> -Rp {{ number_format($discount, 0, ',', '.') }}</p>
                            <p><strong>Final Price:</strong> Rp {{ number_format($finalPrice, 0, ',', '.') }}</p>
                        @endif
                        <p><strong>Amount Paid:</strong> Rp {{ number_format($transaction->total_pay, 0, ',', '.') }}</p>
                        <p><strong>Change:</strong> Rp {{ number_format($transaction->total_return, 0, ',', '.') }}</p>
                        @if($transaction->customer->status === 'new')
                            <p class="text-sm text-yellow-600 mt-2">Points earned will be available for discount on your next purchase!</p>
                        @endif
                    </div>

                    <div class="flex justify-end mt-4">
                        <a href="{{ route('transactions.index') }}"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 mr-2">
                            Back to Transactions
                        </a>
                        <a href="{{ route('transactions.receipt', $transaction->id) }}"
                            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                            Download Receipt
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
