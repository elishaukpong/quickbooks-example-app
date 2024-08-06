<!-- resources/views/connect-quickbooks.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect QuickBooks</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white p-6 rounded shadow-md text-center">
    <h1 class="text-2xl mb-4">Connect to QuickBooks</h1>
    <a href="{{ route('quickbooks.auth') }}" class="bg-blue-500 text-white py-2 px-4 rounded">
        Connect QuickBooks
    </a>
</div>
</body>
</html>
