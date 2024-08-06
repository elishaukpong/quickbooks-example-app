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
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Heading 1</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Heading 2</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Heading 3</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Heading 4</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="border-b">
                        <td class="px-4 py-2 text-sm text-gray-700">Data 1</td>
                        <td class="px-4 py-2 text-sm text-gray-700">Data 2</td>
                        <td class="px-4 py-2 text-sm text-gray-700">Data 3</td>
                        <td class="px-4 py-2 text-sm text-gray-700">Data 4</td>
                    </tr>
                    <tr class="border-b">
                        <td class="px-4 py-2 text-sm text-gray-700">Data 5</td>
                        <td class="px-4 py-2 text-sm text-gray-700">Data 6</td>
                        <td class="px-4 py-2 text-sm text-gray-700">Data 7</td>
                        <td class="px-4 py-2 text-sm text-gray-700">Data 8</td>
                    </tr>
                    <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>



