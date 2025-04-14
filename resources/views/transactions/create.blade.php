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
                            <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer</label>
                            <select name="customer_id" id="customer_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Non-Member</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->no_telp }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Products</label>
                            <div id="product-list">
                                @foreach ($products as $product)
                                    <div class="flex items-center mb-2 {{ $product->stock <= 0 ? 'opacity-50' : '' }}">

                                        <input type="checkbox" name="products[{{ $product->id }}][selected]" value="1"
                                            class="mr-2 rounded border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-300 disabled:border-gray-400 disabled:cursor-not-allowed"
                                            {{ $product->stock <= 0 ? 'disabled' : '' }}>

                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                            class="w-10 h-10 object-cover rounded-md mr-2">

                                        <span class="mr-4">
                                            {{ $product->name }}
                                            (Price: Rp {{ number_format($product->price, 0, ',', '.') }},
                                            Stock: {{ $product->stock }})
                                        </span>

                                        <input type="number" name="products[{{ $product->id }}][quantity]" min="1"
                                            placeholder="Quantity"
                                            class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100 disabled:border-gray-300 disabled:cursor-not-allowed"
                                            {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                    </div>
                                @endforeach
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

            productList.addEventListener('input', handleInputChange);
            totalPayInput.addEventListener('input', handleInputChange);

            function handleInputChange() {
                const totalPrice = updateTotalPrice();
                validatePayment(totalPrice);
            }

            function updateTotalPrice() {
                let totalPrice = 0;

                productList.querySelectorAll('div').forEach(productRow => {
                    const checkbox = productRow.querySelector('input[type="checkbox"]');
                    const quantityInput = productRow.querySelector('input[type="number"]');
                    const priceText = productRow.querySelector('span').textContent;

                    if (checkbox.checked) {
                        const price = parseInt(priceText.match(/Price: Rp ([\d.]+)/)[1].replace(/\./g, ''));
                        const quantity = parseInt(quantityInput.value) || 0;
                        totalPrice += price * quantity;
                    }
                });

                totalPriceDisplay.textContent = `Total Price: Rp ${totalPrice.toLocaleString('id-ID')}`;
                return totalPrice;
            }

            function validatePayment(totalPrice) {
                const totalPay = parseInt(totalPayInput.value) || 0;

                if (totalPay < totalPrice) {
                    paymentWarning.classList.remove('hidden');
                } else {
                    paymentWarning.classList.add('hidden');
                }
            }

            form.addEventListener('submit', function (e) {
                const totalPrice = updateTotalPrice();
                const totalPay = parseInt(totalPayInput.value) || 0;

                if (totalPay < totalPrice) {
                    e.preventDefault();
                    alert('Total payment is less than the total price of the selected products. Please adjust the payment.');
                }
            });
        });
    </script>
</x-app-layout>
