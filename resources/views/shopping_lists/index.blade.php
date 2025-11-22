<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Llistes de la Compra</title>
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
        .card-hover {
            transition: transform 0.3s ease, shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-200 min-h-screen p-4">

    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold mb-8 text-center text-gray-800">Llistes de la Compra</h1>

        <!-- Missatge d'èxit -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 fade-in">
                {{ session('success') }}
            </div>
        @endif

        <!-- Errors del formulari -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 fade-in">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Crear nova llista i Unir-se a una llista -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-md fade-in card-hover">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Crear Nova Llista</h2>
                <a href="{{ route('shopping_lists.create') }}" class="block bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg text-center transition-all duration-300">
                    <i class="fas fa-plus mr-2"></i> Crear
                </a>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md fade-in card-hover">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Unir-se a una Llista</h2>
                <form action="{{ route('shopping_lists.join') }}" method="POST" class="flex space-x-2">
                    @csrf
                    <input type="text" name="share_code" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-400 focus:border-transparent transition-shadow" placeholder="Introdueix la clau (ex. X7B9K2)" maxlength="6" required>
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300">
                        <i class="fas fa-user-plus mr-2"></i> Unir-se
                    </button>
                </form>
            </div>
        </div>

        <!-- Llistes de la compra -->
        @if (empty($shoppingLists))
            <p class="text-gray-600 text-center fade-in">No tens cap llista de la compra. Crea'n una!</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($shoppingLists as $listId => $list)
                <div class="bg-white p-6 rounded-xl shadow-md card-hover fade-in">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ $list['name'] ?? 'Llista sense nom' }}</h2>
                    <div class="flex space-x-3">
                        <a href="{{ route('shopping_lists.show', $listId) }}" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg text-center transition-all duration-300">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('shopping_lists.edit', $listId) }}" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg text-center transition-all duration-300">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('shopping_lists.destroy', $listId) }}" method="POST" onsubmit="return confirm('Segur que vols eliminar aquesta llista?');" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>