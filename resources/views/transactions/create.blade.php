<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- notification section at the top -->
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button onclick="this.parentElement.remove()" class="absolute top-0 right-0 mt-4 mr-4">
                        <svg class="h-4 w-4 fill-current" role="button" viewBox="0 0 20 20">
                            <title>Close</title>
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </button>
                </div>
            @endif

            <!-- session messages -->
            @if (session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <p>{{ session('error') }}</p>
                    <button onclick="this.parentElement.remove()" class="absolute top-0 right-0 mt-4 mr-4">
                        <svg class="h-4 w-4 fill-current" role="button" viewBox="0 0 20 20">
                            <title>Close</title>
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </button>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('transactions.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="customer_type" class="block text-sm font-medium text-gray-700">Customer Type</label>
                            <select name="customer_type" id="customer_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="non-member">Non-Member</option>
                                <option value="member">Member</option>
                            </select>
                        </div>

                        <div id="member-form" class="mb-4 hidden">
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700">Telephone Number</label>
                            <input type="text" name="customer_phone" id="customer_phone"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Enter telephone number">
                            <p id="member-status" class="text-sm mt-2"></p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Products</label>
                            <div id="product-list">
                                <div class="flex items-center space-x-4 mb-4">
                                    <select id="productEntriesSelect" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="10">10 entries</option>
                                        <option value="25">25 entries</option>
                                        <option value="50">50 entries</option>
                                        <option value="100">100 entries</option>
                                    </select>

                                    <div class="relative flex-1 max-w-md">
                                        <input type="text"
                                            id="productSearchInput"
                                            placeholder="Search products by name, price or stock..."
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-10 pr-4 py-2">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <table class="table-auto w-full border-collapse border border-gray-300">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-4 py-2">Select</th>
                                            <th class="border border-gray-300 px-4 py-2">Image</th>
                                            <th class="border border-gray-300 px-4 py-2">Product Name</th>
                                            <th class="border border-gray-300 px-4 py-2">Price</th>
                                            <th class="border border-gray-300 px-4 py-2">Stock</th>
                                            <th class="border border-gray-300 px-4 py-2">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($products as $product)
                                            <tr class="{{ $product->stock <= 0 ? 'opacity-50' : '' }}">
                                                <td class="border border-gray-300 px-4 py-2 text-center">
                                                    <input type="checkbox" name="products[{{ $product->id }}][selected]" value="1"
                                                        class="rounded border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-300 disabled:border-gray-400 disabled:cursor-not-allowed"
                                                        {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                                </td>
                                                <td class="border border-gray-300 px-4 py-2 text-center">
                                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                                        class="w-10 h-10 object-cover rounded-md">
                                                </td>
                                                <td class="border border-gray-300 px-4 py-2">{{ $product->name }}</td>
                                                <td class="border border-gray-300 px-4 py-2">
                                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                                </td>
                                                <td class="border border-gray-300 px-4 py-2">{{ $product->stock }}</td>
                                                <td class="border border-gray-300 px-4 py-2">
                                                    <input type="number" name="products[{{ $product->id }}][quantity]" min="1"
                                                        placeholder="Quantity"
                                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100 disabled:border-gray-300 disabled:cursor-not-allowed"
                                                        value=""
                                                        {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="mt-4 flex items-center justify-between">
                                    <div class="text-sm text-gray-700">
                                        Showing <span id="productStartEntry">1</span> to <span id="productEndEntry">10</span> of <span id="productTotalEntries">0</span> entries
                                    </div>
                                    <div class="flex space-x-2">
                                        <button id="productPrevPage" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 disabled:opacity-50">
                                            Previous
                                        </button>
                                        <div id="productPageNumbers" class="flex space-x-1">
                                        </div>
                                        <button id="productNextPage" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 disabled:opacity-50">
                                            Next
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="total_price_display" class="mb-4 text-lg font-semibold text-gray-700">
                            Total Price: Rp 0
                        </div>

                        <div class="mb-4">
                            <label for="total_pay" class="block text-sm font-medium text-gray-700">Total Pay</label>
                            <input type="number" name="total_pay" id="total_pay" min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required>
                            <p id="payment-warning" class="text-red-500 text-sm mt-2 hidden">
                                Total payment is less than the total price of the selected products.
                            </p>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('transactions.index') }}"
                                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 mr-2">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Save Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const productList = document.getElementById('product-list');
            const totalPayInput = document.getElementById('total_pay');
            const totalPriceDisplay = document.getElementById('total_price_display');
            const paymentWarning = document.getElementById('payment-warning');
            const form = document.querySelector('form');
            const customerTypeSelect = document.getElementById('customer_type');
            const memberForm = document.getElementById('member-form');
            const customerPhoneInput = document.getElementById('customer_phone');
            const memberStatus = document.getElementById('member-status');

            const productSearchInput = document.getElementById('productSearchInput');
            const productEntriesSelect = document.getElementById('productEntriesSelect');
            const productRows = [...document.querySelectorAll('#product-list tbody tr')];
            let productCurrentPage = 1;
            let productEntriesPerPage = parseInt(productEntriesSelect.value);
            let productDebounceTimer;

            customerTypeSelect.addEventListener('change', function () {
                if (this.value === 'member') {
                    memberForm.classList.remove('hidden');
                } else {
                    memberForm.classList.add('hidden');
                    customerPhoneInput.value = '';
                    memberStatus.textContent = '';
                }
            });

            productList.addEventListener('input', updateTotalPrice);

            totalPayInput.addEventListener('input', validatePayment);

            form.addEventListener('submit', function (e) {
                const totalPrice = updateTotalPrice();
                const totalPay = parseInt(totalPayInput.value) || 0;
                const customerType = customerTypeSelect.value;
                let hasProducts = false;

                productList.querySelectorAll('tbody tr').forEach(productRow => {
                    const checkbox = productRow.querySelector('input[type="checkbox"]');
                    const quantity = productRow.querySelector('input[type="number"]').value;
                    if (checkbox && checkbox.checked && quantity > 0) {
                        hasProducts = true;
                    }
                });

                if (!hasProducts) {
                    e.preventDefault();
                    alert('Please select at least one product and specify its quantity.');
                    return;
                }

                if (customerType === 'member' && !customerPhoneInput.value.trim()) {
                    e.preventDefault();
                    alert('Please enter a valid telephone number for the member.');
                    return;
                }

                if (totalPay < totalPrice) {
                    e.preventDefault();
                    alert('Total payment is less than the total price. Please check the payment amount.');
                    return;
                }
            });

            customerPhoneInput.addEventListener('blur', async function() {
                if (customerTypeSelect.value === 'member' && this.value.trim()) {
                    try {
                        const response = await fetch(`/check-customer?phone=${this.value.trim()}`);
                        const data = await response.json();

                        if (data.exists) {
                            memberStatus.textContent = `Existing member: ${data.name}`;
                            memberStatus.classList.remove('text-red-500');
                            memberStatus.classList.add('text-green-500');
                        } else {
                            memberStatus.textContent = 'New member';
                            memberStatus.classList.remove('text-green-500');
                            memberStatus.classList.add('text-blue-500');
                        }
                    } catch (error) {
                        console.error('Error checking customer:', error);
                    }
                }
            });

            function updateTotalPrice() {
                let totalPrice = 0;

                productList.querySelectorAll('tbody tr').forEach(productRow => {
                    const checkbox = productRow.querySelector('input[type="checkbox"]');
                    const quantityInput = productRow.querySelector('input[type="number"]');
                    const priceText = productRow.querySelector('td:nth-child(4)').textContent;

                    if (checkbox && checkbox.checked) {
                        const price = parseInt(priceText.match(/Rp ([\d.]+)/)[1].replace(/\./g, '')) || 0;
                        const quantity = parseInt(quantityInput.value) || 0;
                        totalPrice += price * quantity;
                    }
                });

                totalPriceDisplay.textContent = `Total Price: Rp ${totalPrice.toLocaleString('id-ID')}`;
                return totalPrice;
            }

            function validatePayment() {
                const totalPrice = updateTotalPrice();
                const totalPay = parseInt(totalPayInput.value) || 0;

                if (totalPay < totalPrice) {
                    paymentWarning.classList.remove('hidden');
                } else {
                    paymentWarning.classList.add('hidden');
                }
            }

            function updateProductTable() {
                const searchTerm = productSearchInput.value.toLowerCase();
                const filteredRows = productRows.filter(row => {
                    const name = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const price = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                    const stock = row.querySelector('td:nth-child(5)').textContent.toLowerCase();

                    return name.includes(searchTerm) ||
                        price.includes(searchTerm) ||
                        stock.includes(searchTerm);
                });

                const totalPages = Math.ceil(filteredRows.length / productEntriesPerPage);
                const startIndex = (productCurrentPage - 1) * productEntriesPerPage;
                const endIndex = Math.min(startIndex + productEntriesPerPage, filteredRows.length);

                document.getElementById('productStartEntry').textContent = filteredRows.length ? startIndex + 1 : 0;
                document.getElementById('productEndEntry').textContent = endIndex;
                document.getElementById('productTotalEntries').textContent = filteredRows.length;

                productRows.forEach(row => row.classList.add('hidden'));

                filteredRows.slice(startIndex, endIndex).forEach(row => row.classList.remove('hidden'));

                updateProductPagination(totalPages);
            }

            function updateProductPagination(totalPages) {
                const pageNumbers = document.getElementById('productPageNumbers');
                pageNumbers.innerHTML = '';

                document.getElementById('productPrevPage').disabled = productCurrentPage === 1;

                for (let i = 1; i <= totalPages; i++) {
                    const button = document.createElement('button');
                    button.textContent = i;
                    button.className = `px-3 py-1 rounded-md ${productCurrentPage === i ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`;
                    button.addEventListener('click', () => {
                        productCurrentPage = i;
                        updateProductTable();
                    });
                    pageNumbers.appendChild(button);
                }

                document.getElementById('productNextPage').disabled = productCurrentPage === totalPages;
            }

            productSearchInput.addEventListener('input', function(e) {
                clearTimeout(productDebounceTimer);
                productDebounceTimer = setTimeout(() => {
                    productCurrentPage = 1;
                    updateProductTable();
                }, 300);
            });

            productEntriesSelect.addEventListener('change', function() {
                productEntriesPerPage = parseInt(this.value);
                productCurrentPage = 1;
                updateProductTable();
            });

            document.getElementById('productPrevPage').addEventListener('click', function() {
                if (productCurrentPage > 1) {
                    productCurrentPage--;
                    updateProductTable();
                }
            });

            document.getElementById('productNextPage').addEventListener('click', function() {
                const totalPages = Math.ceil(productRows.length / productEntriesPerPage);
                if (productCurrentPage < totalPages) {
                    productCurrentPage++;
                    updateProductTable();
                }
            });

            updateProductTable();
        });
    </script>
</x-app-layout>
