<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Llista</title>
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
        
        {{-- Botón de volver --}}
        <a href="{{ route('shopping_lists.index') }}" 
           class="inline-flex items-center mb-6 text-gray-600 hover:text-gray-800 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Tornar
        </a>

        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Editar Llista</h1>

        <form method="POST" action="{{ route('shopping_lists.update', $listId) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Campo nombre --}}
            <div class="fade-in">
                <label class="block text-gray-700 font-semibold mb-2">Nom de la llista</label>
                <input type="text" 
                       name="name" 
                       value="{{ $shoppingList['name'] }}" 
                       required
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-transparent transition-shadow" />
            </div>

            {{-- Botón actualizar --}}
            <button type="submit" 
                    class="w-full bg-green-500 text-white font-bold py-3 rounded-lg hover:bg-green-600 hover:shadow-md transition-all duration-300">
                Actualitzar
            </button>
        </form>

    </div>

</body>
</html>