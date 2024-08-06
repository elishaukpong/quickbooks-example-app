<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quick Books') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(! auth()->user()->quickbooks()->exists())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 rounded shadow-md text-center">
                    <h1 class="text-2xl mb-4">Connect to QuickBooks</h1>
                    <a href="{{ route('quickbooks.auth') }}" class="bg-blue-500 text-white py-2 px-4 rounded">
                        Connect QuickBooks
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>



