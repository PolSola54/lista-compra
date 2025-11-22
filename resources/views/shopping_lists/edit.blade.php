<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Llista</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4">

    <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
        
        {{-- Botón de volver --}}
        <a href="{{ route('shopping_lists.index') }}" 
           class="inline-block mb-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">
            Tornar
        </a>

        <h1 class="text-2xl font-bold mb-4">Editar Llista</h1>

        <form method="POST" action="{{ route('shopping_lists.update', $listId) }}" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Campo nombre --}}
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Nom de la llista</label>
                <input type="text" 
                       name="name" 
                       value="{{ $shoppingList['name'] }}" 
                       required
                       class="w-full border rounded px-3 py-2 focus:ring focus:ring-green-300" />
            </div>

            {{-- Botón actualizar --}}
            <button type="submit" 
                    class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                Actualitzar
            </button>
        </form>

    </div>

</body>
</html>
