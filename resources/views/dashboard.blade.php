<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 font-semibold text-xl text-gray-900">
                    {{ __("Welcome, " . auth()->user()->name . "!") }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold">Jumlah Pembelian Per Hari (Bulan Ini)</h3>
                        <ul class="mt-4">
                            @foreach ($dailyPurchases as $purchase)
                                <li class="text-sm">
                                    <span class="font-semibold">{{ $purchase->date }}:</span>
                                    <span>{{ $purchase->total }} pembelian</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold">Statistik Pembelian Produk</h3>
                        <canvas id="productChart" class="mt-4"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const productData = @json($productPurchases);

            const productNames = Object.values(productData).map(item => item.product_name);
            const productTotals = Object.values(productData).map(item => item.total);

            const ctx = document.getElementById('productChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: productNames,
                    datasets: [{
                        label: 'Jumlah Pembelian',
                        data: productTotals,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
