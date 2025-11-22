<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Llista</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-200 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 transform transition-all duration-300 hover:shadow-xl">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Crear Nova Llista</h1>
        <form action="{{ route('shopping_lists.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="fade-in">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom de la llista</label>
                <input type="text" name="name" id="name" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-shadow" required>
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-lg hover:shadow-md transition-all duration-300">
                    Crear
                </button>
                <a href="{{ route('shopping_lists.index') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 rounded-lg text-center hover:shadow-md transition-all duration-300">
                    Cancel·lar
                </a>
            </div>
        </form>
    </div>
</body>
</html>