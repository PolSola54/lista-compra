<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $shoppingList['name'] ?? 'Llista sense nom' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center">{{ $shoppingList['name'] ?? 'Llista sense nom' }}</h1>

        <!-- Clau per compartir -->
        <div class="mb-4">
            <p class="text-gray-700">Clau per compartir: <span class="font-semibold">{{ $shoppingList['share_code'] }}</span></p>
            <p class="text-sm text-gray-500">Comparteix aquesta clau perquè altres usuaris s’hi uneixin.</p>
        </div>

        <!-- Missatge d'èxit -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Formulari per afegir ítem -->
        <div class="mb-6">
            <form action="{{ route('shopping_lists.items.store', $listId) }}" method="POST">
                @csrf
                <div class="flex space-x-2">
                    <input type="text" name="name" class="p-2 w-full border rounded" placeholder="Afegir ítem..." required>
                    <input type="text" name="tag" class="p-2 w-full border rounded" placeholder="Etiqueta (ex. Bonpreu-Esclat)">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Afegir Ítem
                    </button>
                </div>
            </form>
        </div>

        <!-- Categories i ítems -->
        @if (empty($categories))
            <p class="text-gray-600">Aquesta llista no té cap categoria ni ítem. Afegeix-ne un!</p>
        @else
            <div class="space-y-4">
                @foreach ($categories as $categoryId => $category)
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-xl font-semibold">{{ $category['name'] }}</h2>
                        <form action="{{ route('shopping_lists.categories.destroy', [$listId, $categoryId]) }}" method="POST" onsubmit="return confirm('Segur que vols eliminar aquesta categoria?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">
                                Eliminar Categoria
                            </button>
                        </form>
                    </div>
                    <ul class="space-y-2">
                        @foreach ($items[$categoryId] ?? [] as $itemId => $item)
                        <li class="flex items-center justify-between">
                            <div class="flex items-center">
                                <form action="{{ route('shopping_lists.items.update', [$listId, $itemId]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="category_id" value="{{ $categoryId }}">
                                    <input type="checkbox" name="is_completed" value="1" {{ $item['is_completed'] ? 'checked' : '' }} onchange="this.form.submit()" class="mr-2" id="item-{{ $itemId }}">
                                </form>
                                <span class="{{ $item['is_completed'] ? 'line-through text-gray-500' : '' }}">
                                    {{ $item['name'] }} {{ $item['tag'] ?? '' ? "({$item['tag']})" : '' }}
                                </span>
                            </div>
                            <form action="{{ route('shopping_lists.items.destroy', [$listId, $itemId]) }}" method="POST" onsubmit="return confirm('Segur que vols eliminar aquest ítem?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="category_id" value="{{ $categoryId }}">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">
                                    Eliminar
                                </button>
                            </form>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('shopping_lists.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Tornar a les llistes
            </a>
        </div>
    </div>
</body>
</html>