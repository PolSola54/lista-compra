<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $shoppingList['name'] }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center">{{ $shoppingList['name'] }}</h1>

        <!-- Botó per tornar a l'índex -->
        <div class="mb-4">
            <a href="{{ route('shopping_lists.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Tornar a les Llistes
            </a>
        </div>

        <!-- Missatge d'èxit -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Formulari per afegir un nou ítem -->
        <div class="mb-6">
            <form action="{{ route('shopping_lists.items.store', $listId) }}" method="POST" class="flex space-x-2">
                @csrf
                <input type="text" name="name" placeholder="Nom del producte (ex. Llet)" class="p-2 w-full border rounded" required>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Afegir Ítem
                </button>
            </form>
        </div>

        <!-- Llista de categories i ítems -->
        @if (empty($categories))
            <p class="text-gray-600">Aquesta llista no té cap categoria ni ítem. Afegeix-ne un!</p>
        @else
            <div class="space-y-6">
                @foreach ($categories as $categoryId => $category)
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-2">{{ $category['name'] }}</h2>
                    <div class="space-y-2">
                        @php
                            $items = $firebase->get("items/$listId/$categoryId") ?? [];
                        @endphp
                        @if (empty($items))
                            <p class="text-gray-600">Cap ítem en aquesta categoria.</p>
                        @else
                            @foreach ($items as $itemId => $item)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <form action="{{ route('shopping_lists.items.update', [$listId, $itemId]) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="category_id" value="{{ $categoryId }}">
                                        <input type="checkbox" name="is_completed" {{ $item['is_completed'] ? 'checked' : '' }} onchange="this.form.submit()" class="mr-2">
                                    </form>
                                    <span class="{{ $item['is_completed'] ? 'line-through text-gray-500' : '' }}">
                                        {{ $item['name'] }}
                                    </span>
                                </div>
                                <form action="{{ route('shopping_lists.items.destroy', [$listId, $itemId]) }}" method="POST" onsubmit="return confirm('Segur que vols eliminar aquest ítem?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="category_id" value="{{ $categoryId }}">
                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        @endif
                    </div>
                    <!-- Botó per eliminar categoria -->
                    <form action="{{ route('shopping_lists.categories.destroy', [$listId, $categoryId]) }}" method="POST" onsubmit="return confirm('Segur que vols eliminar aquesta categoria i els seus ítems?');" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">
                            Eliminar Categoria
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>