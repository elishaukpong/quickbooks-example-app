<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Expenses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-x-auto">
                <table class="w-full bg-white border border-gray-200">
                    <thead>
                    <tr class="w-full bg-gray-100 border-b border-gray-200">
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Account Id</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Amount</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Currency</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Method</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($expenses as $expense)
                        <tr class="border-b">
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $expense->Id }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $expense->TotalAmt }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $expense->CurrencyRef }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $expense->PaymentType }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $expense->TxnDate }}</td>
                        </tr>
                    @empty
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 rounded shadow-md text-center">
                            <h1 class="text-2xl mb-4">No Information Yet</h1>
                        </div>
                    @endforelse

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>



