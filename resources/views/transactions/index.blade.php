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
                        <div class="flex items-center space-x-4 flex-1">
                            <h3 class="text-lg font-semibold">Transaction List</h3>
                            <div class="flex items-center space-x-2">

                                <select id="entriesSelect" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="10">10 entries</option>
                                    <option value="25">25 entries</option>
                                    <option value="50">50 entries</option>
                                    <option value="100">100 entries</option>
                                </select>

                                <div class="relative flex-1 max-w-md">
                                    <input type="text"
                                        id="searchInput"
                                        placeholder="Search by customer, staff, or date..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-10 pr-4 py-2"
                                    >
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <span id="startEntry">1</span> to <span id="endEntry">10</span> of <span id="totalEntries">0</span> entries
                        </div>
                        <div class="flex space-x-2">
                            <button id="prevPage" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 disabled:opacity-50">
                                Previous
                            </button>
                            <div id="pageNumbers" class="flex space-x-1">
                            </div>
                            <button id="nextPage" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 disabled:opacity-50">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="detailsModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Transaction Details</h3>
            <div id="modalContent" class="space-y-2">
            </div>
            <div class="products-list mt-4">
                <h4 class="font-semibold mb-2">Products:</h4>
                <div class="max-h-[250px] overflow-y-auto">
                    <ul id="productsList" class="space-y-2">
                    </ul>
                </div>
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
            const productsList = document.getElementById('productsList');
            const selectedTransaction = transactions.find(t => t.id === transactionId);

            if (selectedTransaction) {
                modalContent.innerHTML = `
                    <p><strong>Date:</strong> ${selectedTransaction.sale_date}</p>
                    <p><strong>Customer:</strong> ${selectedTransaction.customer?.name ?? 'Non-Member'}</p>
                    <p><strong>Staff:</strong> ${selectedTransaction.staff.name}</p>
                    <p><strong>Total Price:</strong> Rp ${selectedTransaction.total_price.toLocaleString('id-ID')}</p>
                    <p><strong>Total Pay:</strong> Rp ${selectedTransaction.total_pay.toLocaleString('id-ID')}</p>
                    <p><strong>Total Return:</strong> Rp ${selectedTransaction.total_return.toLocaleString('id-ID')}</p>
                `;

                productsList.innerHTML = '';
                selectedTransaction.detail_sales.forEach(detail => {
                    const imageUrl = `${baseUrl}/${detail.product.image}`;
                    const li = document.createElement('li');
                    li.className = 'flex items-center p-2 hover:bg-gray-50 rounded';
                    li.innerHTML = `
                        <div class="flex items-center space-x-3 w-full">
                            <img src="${imageUrl}" alt="${detail.product.name}"
                                class="w-12 h-12 object-cover rounded-md">
                            <div class="flex-1">
                                <p class="font-medium">${detail.product.name}</p>
                                <p class="text-sm text-gray-600">Quantity: ${detail.amount}</p>
                                <p class="text-sm text-gray-600">Subtotal: Rp ${detail.sub_total.toLocaleString('id-ID')}</p>
                            </div>
                        </div>
                    `;
                    productsList.appendChild(li);
                });
            }

            document.getElementById('detailsModal').classList.remove('hidden');
        }

        function hideDetails() {
            document.getElementById('detailsModal').classList.add('hidden');
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const entriesSelect = document.getElementById('entriesSelect');
            const tbody = document.querySelector('tbody');
            const originalRows = [...tbody.querySelectorAll('tr')];
            let currentPage = 1;
            let entriesPerPage = parseInt(entriesSelect.value);
            let debounceTimer;

            function updateTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const filteredRows = originalRows.filter(row => {
                    const date = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                    const customer = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const staff = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const price = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

                    return date.includes(searchTerm) ||
                           customer.includes(searchTerm) ||
                           staff.includes(searchTerm) ||
                           price.includes(searchTerm);
                });

                const totalPages = Math.ceil(filteredRows.length / entriesPerPage);
                const startIndex = (currentPage - 1) * entriesPerPage;
                const endIndex = Math.min(startIndex + entriesPerPage, filteredRows.length);

                document.getElementById('startEntry').textContent = filteredRows.length ? startIndex + 1 : 0;
                document.getElementById('endEntry').textContent = endIndex;
                document.getElementById('totalEntries').textContent = filteredRows.length;

                tbody.innerHTML = '';

                if (filteredRows.length === 0) {
                    const noResultsRow = document.createElement('tr');
                    noResultsRow.innerHTML = `
                        <td colspan="7" class="border border-gray-300 px-4 py-2 text-center text-gray-500">
                            No results found
                        </td>
                    `;
                    tbody.appendChild(noResultsRow);
                } else {
                    filteredRows.slice(startIndex, endIndex).forEach(row => {
                        tbody.appendChild(row.cloneNode(true));
                    });
                }

                updatePagination(totalPages);
            }

            function updatePagination(totalPages) {
                const pageNumbers = document.getElementById('pageNumbers');
                pageNumbers.innerHTML = '';

                document.getElementById('prevPage').disabled = currentPage === 1;

                for (let i = 1; i <= totalPages; i++) {
                    const button = document.createElement('button');
                    button.textContent = i;
                    button.className = `px-3 py-1 rounded-md ${currentPage === i ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`;
                    button.addEventListener('click', () => {
                        currentPage = i;
                        updateTable();
                    });
                    pageNumbers.appendChild(button);
                }

                document.getElementById('nextPage').disabled = currentPage === totalPages;
            }

            searchInput.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    currentPage = 1;
                    updateTable();
                }, 300);
            });

            entriesSelect.addEventListener('change', function() {
                entriesPerPage = parseInt(this.value);
                currentPage = 1;
                updateTable();
            });

            document.getElementById('prevPage').addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    updateTable();
                }
            });

            document.getElementById('nextPage').addEventListener('click', function() {
                const totalPages = Math.ceil(originalRows.length / entriesPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    updateTable();
                }
            });

            updateTable();
        });
    </script>

    <style>
        .max-h-[250px] {
            max-height: 250px;
        }

        .products-list ul::-webkit-scrollbar {
            width: 6px;
        }

        .products-list ul::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .products-list ul::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .products-list ul::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</x-app-layout>
