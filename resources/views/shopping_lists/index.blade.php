<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Llistes de la Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center">Llistes de la Compra</h1>

        <!-- Missatge d'èxit -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Errors del formulari -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Crear nova llista -->
        <div class="mb-4">
            <a href="{{ route('shopping_lists.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Crear Nova Llista
            </a>
        </div>

        <!-- Unir-se a una llista -->
        <div class="mb-6">
            <form action="{{ route('shopping_lists.join') }}" method="POST" class="flex space-x-2">
                @csrf
                <input type="text" name="share_code" class="p-2 w-full border rounded" placeholder="Introdueix la clau de la llista (ex. X7B9K2)" maxlength="6" required>
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Unir-se a la Llista
                </button>
            </form>
        </div>

        <!-- Llistes de la compra -->
        @if (empty($shoppingLists))
            <p class="text-gray-600">No tens cap llista de la compra. Crea'n una!</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($shoppingLists as $listId => $list)
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-2">{{ $list['name'] ?? 'Llista sense nom' }}</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('shopping_lists.show', $listId) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Veure
                        </a>
                        <a href="{{ route('shopping_lists.edit', $listId) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                            Editar
                        </a>
                        <form action="{{ route('shopping_lists.destroy', $listId) }}" method="POST" onsubmit="return confirm('Segur que vols eliminar aquesta llista?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Eliminar
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