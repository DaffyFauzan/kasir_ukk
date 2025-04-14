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
                        <h3 class="text-lg font-semibold">Product List</h3>
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
                                <th class="border border-gray-300 px-4 py-2 text-center">Actions</th>
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
                                    <td class="border border-gray-300 px-4 py-2 text-center">
                                        @if(Auth::check() && Auth::user()->role !== 'Staff')
                                            <button class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                                <a href="{{ route('products.edit', $product) }}">
                                                    Edit
                                                </a>
                                            </button>
                                            <button onclick="showModal({{ $product->id }})"
                                                class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600">
                                                Delete
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($products->isEmpty())
                        <div class="mt-4 text-center text-gray-500">
                            No products available.
                        </div>
                    @endif
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
    </script>

</x-app-layout>
