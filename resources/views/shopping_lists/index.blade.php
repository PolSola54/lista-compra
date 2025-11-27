<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Llistes de la Compra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .card-hover { transition: all 0.3s; }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-200 min-h-screen p-6">

    <div class="max-w-6xl mx-auto">
        <h1 class="text-5xl font-bold mb-10 text-center text-gray-800">Llistes de la Compra</h1>

        <!-- Missatges -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-xl mb-8 fade-in text-center text-lg">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-xl mb-8 fade-in">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Crear i unir-se -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <div class="bg-white p-8 rounded-2xl shadow-lg card-hover text-center">
                <h2 class="text-2xl font-semibold mb-6 text-gray-800">Crear Nova Llista</h2>
                <a href="{{ route('shopping_lists.create') }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 px-8 rounded-xl text-lg transition-all">
                    <i class="fas fa-plus mr-3"></i> Crear nova llista
                </a>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-lg card-hover text-center">
                <h2 class="text-2xl font-semibold mb-6 text-gray-800">Unir-se a una Llista</h2>
                <form action="{{ route('shopping_lists.join') }}" method="POST" class="flex flex-col sm:flex-row gap-4">
                    @csrf
                    <input type="text" name="share_code" class="flex-1 p-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-300 transition" placeholder="Clau de 6 caràcters" maxlength="6" required>
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-8 rounded-xl transition-all">
                        <i class="fas fa-user-plus mr-3"></i> Unir-se
                    </button>
                </form>
            </div>
        </div>

        <!-- Llistes existents -->
        @if (empty($shoppingLists))
            <p class="text-center text-gray-600 text-xl fade-in">Encara no tens cap llista. Crea la primera!</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($shoppingLists as $listId => $list)
                <div class="bg-white rounded-2xl shadow-lg card-hover relative">
                    <a href="{{ route('shopping_lists.show', $listId) }}" class="block p-8 hover:bg-gray-50 transition">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $list['name'] ?? 'Llista sense nom' }}</h3>
                        <p class="text-gray-500 text-sm">Clic per obrir</p>
                    </a>

                    <!-- Menú tres punts -->
                    <div class="absolute top-4 right-4">
                        <button class="text-gray-600 hover:text-gray-900 text-2xl menu-toggle">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-200 z-50">
                            <a href="{{ route('shopping_lists.edit', $listId) }}" class="block px-5 py-3 text-gray-700 hover:bg-gray-100 rounded-t-xl transition">
                                <i class="fas fa-edit mr-3"></i> Editar nom
                            </a>
                            <form action="{{ route('shopping_lists.destroy', $listId) }}" method="POST" onsubmit="return confirm('Segur que vols eliminar aquesta llista?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-left px-5 py-3 text-red-600 hover:bg-red-50 rounded-b-xl transition">
                                    <i class="fas fa-trash mr-3"></i> Eliminar llista
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        // Dropdown dels tres punts
        document.querySelectorAll('.menu-toggle').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                // Tanca tots els altres menús
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    if (menu !== this.nextElementSibling) menu.classList.add('hidden');
                });
                this.nextElementSibling.classList.toggle('hidden');
            });
        });

        // Tanca el menú si cliques fora
        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
        });
    </script>
</body>
</html>