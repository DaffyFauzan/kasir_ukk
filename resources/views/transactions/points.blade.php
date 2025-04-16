<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transaction Points') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Transaction Points</h3>
                    <div class="mb-4">
                        <p><strong>Transaction ID:</strong> {{ $transaction->id }}</p>
                        <p><strong>Customer:</strong> {{ $transaction->customer->name ?? 'Non-Member' }}</p>
                        <p><strong>Member Status:</strong>
                            <span class="{{ $transaction->customer->status === 'new' ? 'text-blue-500' : 'text-green-500' }}">
                                {{ ucfirst($transaction->customer->status) }} Member
                            </span>
                        </p>
                        <p><strong>Points Earned:</strong> {{ $transaction->poin }}</p>
                        <p><strong>Total Points Available:</strong> {{ $transaction->customer->poin ?? 0 }}</p>
                    </div>

                    @if ($transaction->customer)
                        <form action="{{ route('transactions.finalize', $transaction->id) }}" method="POST" class="mt-4">
                            @csrf
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                                <input type="text" name="name" id="name"
                                    value="{{ $transaction->customer->name }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $transaction->customer->status === 'old' ? 'bg-gray-100' : '' }}"
                                    {{ $transaction->customer->status === 'old' ? 'readonly' : '' }}
                                    required>
                                @if ($transaction->customer->status === 'old')
                                    <p class="text-sm text-gray-500 mt-1">Name cannot be changed for existing members.</p>
                                @endif
                            </div>

                            @if($transaction->customer->status === 'old')
                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="use_points" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2">Use previous points for discount (Available: {{ $previousPoints }} points)</span>
                                    </label>
                                    <p class="text-sm text-gray-500 mt-1">Current transaction points ({{ $transaction->poin }}) will be added after purchase</p>
                                </div>
                            @else
                                <p class="text-sm text-yellow-600 mb-4">New members cannot use points for discount yet.</p>
                            @endif

                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('transactions.index') }}"
                                    class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                                    Cancel
                                </a>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                    Submit
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
