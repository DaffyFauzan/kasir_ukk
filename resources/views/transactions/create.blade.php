<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                                        {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
            // DOM Elements
            const productList = document.getElementById('product-list');
            const totalPayInput = document.getElementById('total_pay');
            const totalPriceDisplay = document.getElementById('total_price_display');
            const paymentWarning = document.getElementById('payment-warning');
            const form = document.querySelector('form');
            const customerTypeSelect = document.getElementById('customer_type');
            const memberForm = document.getElementById('member-form');
            const customerPhoneInput = document.getElementById('customer_phone');
            const memberStatus = document.getElementById('member-status');

            // Event: Handle Customer Type Change
            customerTypeSelect.addEventListener('change', function () {
                if (this.value === 'member') {
                    memberForm.classList.remove('hidden'); // Show telephone input for members
                } else {
                    memberForm.classList.add('hidden'); // Hide telephone input for non-members
                    customerPhoneInput.value = ''; // Clear the telephone input
                    memberStatus.textContent = ''; // Clear member status
                }
            });

            // Event: Handle Product Selection and Quantity Input
            productList.addEventListener('input', updateTotalPrice);

            // Event: Handle Total Payment Input
            totalPayInput.addEventListener('input', validatePayment);

            // Event: Handle Form Submission
            form.addEventListener('submit', function (e) {
                const totalPrice = updateTotalPrice();
                const totalPay = parseInt(totalPayInput.value) || 0;
                const customerType = customerTypeSelect.value;

                console.log('Customer Type:', customerType);
                console.log('Total Price:', totalPrice);
                console.log('Total Pay:', totalPay);

                // Skip validation for Non-Member
                if (customerType === 'non-member') {
                    return; // Allow form submission
                }

                // Validate payment for Member
                if (totalPay < totalPrice) {
                    e.preventDefault();
                    alert('Total payment is less than the total price of the selected products. Please adjust the payment.');
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
        });
    </script>
</x-app-layout>
