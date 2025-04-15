<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transaction History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Transaction List</h3>
                        <div class="flex space-x-2">
                            @if(Auth::check() && Auth::user()->role !== 'Administrator')
                                <a href="{{ route('transactions.create') }}"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                    Add Transaction
                                </a>
                            @endif
                            <a href="{{ route('transactions.export') }}"
                                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                                Export to Excel
                            </a>
                        </div>
                    </div>
                    <table class="table-auto w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-2">Date</th>
                                <th class="border border-gray-300 px-4 py-2">Customer</th>
                                <th class="border border-gray-300 px-4 py-2">Staff</th>
                                <th class="border border-gray-300 px-4 py-2">Total Price</th>
                                <th class="border border-gray-300 px-4 py-2">Total Pay</th>
                                <th class="border border-gray-300 px-4 py-2">Total Return</th>
                                <th class="border border-gray-300 px-4 py-2 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="border border-gray-300 px-4 py-2">
                                        {{ \Carbon\Carbon::parse($transaction->created_at)->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        {{ $transaction->customer->name ?? 'Non-Member' }}
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $transaction->staff->name }}</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        {{ 'Rp ' . number_format($transaction->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        {{ 'Rp ' . number_format($transaction->total_pay, 0, ',', '.') }}
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        {{ 'Rp ' . number_format($transaction->total_return, 0, ',', '.') }}
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-center">
                                        <button onclick="showDetails({{ $transaction->id }})"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                            View Details
                                        </button>
                                        <button class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600">
                                            <a href="{{ route('transactions.receipt', $transaction->id) }}">
                                                Export PDF
                                            </a>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($transactions->isEmpty())
                        <div class="mt-4 text-center text-gray-500">
                            No transactions available.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="detailsModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Transaction Details</h3>
            <div id="modalContent">

            </div>
            <div class="flex justify-end mt-4">
                <button onclick="hideDetails()"
                    class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        const transactions = @json($transactions);
        const baseUrl = "{{ url('storage') }}";

        function showDetails(transactionId) {
            const modalContent = document.getElementById('modalContent');
            const selectedTransaction = transactions.find(t => t.id === transactionId);

            if (selectedTransaction) {
                let detailsHtml = `
                    <p><strong>Date:</strong> ${selectedTransaction.sale_date}</p>
                    <p><strong>Customer:</strong> ${selectedTransaction.customer?.name ?? 'Non-Member'}</p>
                    <p><strong>Staff:</strong> ${selectedTransaction.staff.name}</p>
                    <p><strong>Total Price:</strong> Rp ${selectedTransaction.total_price.toLocaleString('id-ID')}</p>
                    <p><strong>Total Pay:</strong> Rp ${selectedTransaction.total_pay.toLocaleString('id-ID')}</p>
                    <p><strong>Total Return:</strong> Rp ${selectedTransaction.total_return.toLocaleString('id-ID')}</p>
                    <h4 class="mt-4 font-semibold">Products:</h4>
                    <ul>
                `;

                selectedTransaction.detail_sales.forEach(detail => {
                    const imageUrl = `${baseUrl}/${detail.product.image}`;
                    detailsHtml += `
                        <li class="flex items-center mb-2">
                            <img src="${imageUrl}" alt="${detail.product.name}"
                                class="w-10 h-10 object-cover rounded-md mr-2">
                            <span>${detail.product.name} (x${detail.amount})</span>
                        </li>
                    `;
                });

                detailsHtml += '</ul>';
                modalContent.innerHTML = detailsHtml;
            }

            document.getElementById('detailsModal').classList.remove('hidden');
        }

        function hideDetails() {
            document.getElementById('detailsModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
