<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-lg font-semibold">Use Points</h2>
                    <form action="{{ route('transactions.finalize', $transaction->id) }}" method="POST">
                        @csrf
                        <p><strong>Total Price:</strong> Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</p>
                        <p><strong>Available Points:</strong> {{ $transaction->customer->poin }}</p>
                        <div class="mb-4">
                            <label>
                                <input type="checkbox" name="use_points" value="1">
                                Use Points as Discount
                            </label>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            Finalize Transaction
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
