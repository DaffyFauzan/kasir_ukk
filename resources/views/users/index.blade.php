<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">User List</h3>
                        <a href="{{ route('users.create') }}"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            Add User
                        </a>
                    </div>
                    <table class="table-auto w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-2">ID</th>
                                <th class="border border-gray-300 px-4 py-2">Name</th>
                                <th class="border border-gray-300 px-4 py-2">Email</th>
                                <th class="border border-gray-300 px-4 py-2">Role</th>
                                <th class="border border-gray-300 px-4 py-2 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="border border-gray-300 px-4 py-2">{{ $user->id }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $user->name }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $user->email }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $user->role }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-center">
                                        <button class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                            <a href="{{ route('users.edit', $user) }}">
                                                Edit
                                            </a>
                                        </button>
                                        <button type="button"
                                            onclick="showModal('{{ $user->id }}')"
                                            class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
