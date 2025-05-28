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
        <h1 class="text-3xl font-bold mb-6 text-center">Les meves Llistes de la Compra</h1>

        <!-- Botó per crear nova llista -->
        <div class="mb-4">
            <a href="{{ route('shopping_lists.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Crear Nova Llista
            </a>
        </div>

        <!-- Missatge d'èxit -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Llista de llistes -->
        @if (empty($shoppingLists))
            <p class="text-gray-600">No tens cap llista de la compra. Crea'n una!</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($shoppingLists as $listId => $list)
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-2">{{ $list['name'] }}</h2>
                    <p class="text-gray-600 mb-4">
                        Creada: {{ \Carbon\Carbon::parse($list['created_at'])->format('d/m/Y') }}
                    </p>
                    <div class="flex space-x-2">
                        <a href="{{ route('shopping_lists.show', $listId) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded">
                            Veure
                        </a>
                        <a href="{{ route('shopping_lists.edit', $listId) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded">
                            Editar
                        </a>
                        <form action="{{ route('shopping_lists.destroy', $listId) }}" method="POST" onsubmit="return confirm('Segur que vols eliminar aquesta llista?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">
                                Eliminar
                            </button>
                        </form>
                        <button onclick="openShareModal('{{ $listId }}')" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-1 px-3 rounded">
                            Compartir
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modal per compartir -->
    <div id="shareModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold mb-4">Compartir Llista</h2>
            <form id="shareForm" method="POST">
                @csrf
                <input type="hidden" name="list_id" id="shareListId">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email de l'usuari</label>
                    <input type="email" name="email" id="email" class="mt-1 p-2 w-full border rounded" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeShareModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel·lar
                    </button>
                    <button type="submit" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                        Compartir
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openShareModal(listId) {
            document.getElementById('shareListId').value = listId;
            document.getElementById('shareForm').action = '{{ url("shopping-lists") }}/' + listId + '/share';
            document.getElementById('shareModal').classList.remove('hidden');
        }

        function closeShareModal() {
            document.getElementById('shareModal').classList.add('hidden');
        }
    </script>
</body>
</html>