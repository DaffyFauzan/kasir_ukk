<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-4 flex-1">
                            <h3 class="text-lg font-semibold">Product List</h3>
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
                                        placeholder="Search products..."
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
                        @if(Auth::check() && Auth::user()->role !== 'Staff')
                            <a href="{{ route('products.create') }}"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Add Product
                            </a>
                        @endif
                    </div>
                    <table class="table-auto w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Image</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Name</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Price</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Stock</th>
                                @if(Auth::check() && Auth::user()->role !== 'Staff')
                                    <th class="border border-gray-300 px-4 py-2 text-center">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="border border-gray-300 px-4 py-2">{{ $loop->iteration }}</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}"
                                                alt="{{ $product->name }}" class="w-16 h-16 object-cover rounded-md">
                                        @else
                                            <span class="text-gray-500">No Image</span>
                                        @endif
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->name }}</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        {{ 'Rp ' . number_format($product->price, 0, ',', '.') }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->stock }}</td>
                                    @if(Auth::check() && Auth::user()->role !== 'Staff')
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <button class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                                <a href="{{ route('products.edit', $product) }}">
                                                    Edit
                                                </a>
                                            </button>
                                            <button onclick="showModal({{ $product->id }})"
                                                class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600">
                                                Delete
                                            </button>
                                            <button onclick="showStockModal({{ $product->id }})"
                                                class="px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600">
                                                Add Stock
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($products->isEmpty())
                        <div class="mt-4 text-center text-gray-500">
                            No products available.
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

    <!-- Modal -->
    <div id="deleteData" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Confirm Deletion</h3>
            <p class="text-gray-700 mb-6">Are you sure you want to delete this product?</p>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end">
                    <button type="button" onclick="hideModal()"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 mr-2">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="addStockModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Add Stock</h3>
            <form id="addStockForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label for="stock" class="block text-gray-700 font-medium mb-2">Stock Quantity</label>
                    <input type="number" id="stock" name="stock" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="hideStockModal()"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 mr-2">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                        Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showModal(productId) {
            const deleteData = document.getElementById('deleteData');
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = `/products/${productId}`;
            deleteData.classList.remove('hidden');
        }

        function hideModal() {
            const deleteData = document.getElementById('deleteData');
            deleteData.classList.add('hidden');
        }

        function showStockModal(productId) {
            const addStockModal = document.getElementById('addStockModal');
            const addStockForm = document.getElementById('addStockForm');
            addStockForm.action = `/products/${productId}/add-stock`;
            addStockModal.classList.remove('hidden');
        }

        function hideStockModal() {
            const addStockModal = document.getElementById('addStockModal');
            addStockModal.classList.add('hidden');
        }

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
                    const id = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                    const name = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const price = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                    const stock = row.querySelector('td:nth-child(5)').textContent.toLowerCase();

                    return id.includes(searchTerm) ||
                           name.includes(searchTerm) ||
                           price.includes(searchTerm) ||
                           stock.includes(searchTerm);
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
                        <td colspan="6" class="border border-gray-300 px-4 py-2 text-center text-gray-500">
                            No products found
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

</x-app-layout>
