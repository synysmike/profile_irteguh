<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Investor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow border border-gray-200 p-6">
        <h1 class="text-xl font-bold text-gray-800">Portal Investor</h1>
        <p class="text-sm text-gray-600 mt-1">Masukkan nomor HP yang terdaftar untuk melihat setoran, dividen, dan dokumen.</p>

        @if(session('success'))
        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded text-green-700 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('investor.portal.login.submit') }}" class="mt-5 space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="08… atau 62…"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-md font-semibold hover:bg-purple-700">Masuk</button>
        </form>
    </div>
</body>
</html>
