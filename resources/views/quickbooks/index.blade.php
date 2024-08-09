<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quick Books') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(auth()->user()->quickbooks()->exists())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 rounded shadow-md text-center">
                    <h1 class="text-2xl mb-4">You have already connected to Quickbooks</h1>

                    @if(auth()->user()->isBuyer())
                        <a href="{{ route('buyer.index') }}" class="bg-blue-500 text-white py-2 px-4 rounded">
                            View Expenses
                        </a>
                    @else
                        <a href="{{ route('supplier.index') }}" class="bg-blue-500 text-white py-2 px-4 rounded">
                            View Sales
                        </a>
                    @endif
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 rounded shadow-md text-center">
                    <h1 class="text-2xl mb-4">Connect to QuickBooks</h1>
                    <a href="{{ route('quickbooks.auth') }}" class="bg-blue-500 text-white py-2 px-4 rounded">
                        Connect QuickBooks
                    </a>
                </div>
            @endif

            @if(auth()->user()->isBuyer() && $accounts)
                    <div class="py-12">
                        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                            <div class="overflow-x-auto">
                                <table class="w-full bg-white border border-gray-200">
                                    <thead>
                                    <tr class="w-full bg-gray-100 border-b border-gray-200">
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Name</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Ref</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Type</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Default</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($accounts as $account)
                                        <tr class="border-b">
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $account->name }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $account->ref }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $account->type }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $account->is_default ? 'Yes' : 'No' }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-700">
                                                <a href="{{ route('accounts.default', $account->id) }}" class="bg-blue-500 text-white py-2 px-4 rounded">
                                                    Make Default
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
            @endif
        </div>
    </div>
</x-app-layout>



