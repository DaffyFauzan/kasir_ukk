<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-lg font-semibold">Transaction Summary</h2>
                    <p><strong>Total Price:</strong> Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</p>
                    <p><strong>Discount:</strong> Rp {{ number_format($discount, 0, ',', '.') }}</p>
                    <p><strong>Final Price:</strong> Rp {{ number_format($finalPrice, 0, ',', '.') }}</p>
                    <div class="flex justify-end mt-4">
                        <a href="{{ route('transactions.index') }}"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 mr-2">
                            Back
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
