<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $shoppingList['name'] ?? 'Llista sense nom' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CDN per Sortable.js -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <!-- CDN per Toastify.js -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        .item-complete {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-200 min-h-screen p-4">

    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">{{ $shoppingList['name'] ?? 'Llista sense nom' }}</h1>

        <div class="mt-8 text-center">
            <a href="{{ route('shopping_lists.index') }}" class="inline-flex items-center bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition-all duration-300">
                <i class="fas fa-arrow-left mr-2"></i> Tornar a les llistes
            </a>
        </div>


        <!-- Clau per compartir -->
        <div class="mb-6 bg-blue-50 p-4 rounded-lg fade-in">
            <p class="text-gray-700">Clau per compartir: <span class="font-semibold bg-blue-100 px-2 py-1 rounded">{{ $shoppingList['share_code'] }}</span></p>
            <p class="text-sm text-gray-500 mt-1">Comparteix aquesta clau perquè altres usuaris s’hi uneixin.</p>
        </div>

        <!-- Missatge d'èxit -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 fade-in">
                {{ session('success') }}
            </div>
        @endif

        <!-- Formulari per afegir ítem -->
        <div class="mb-8 fade-in">
            <form id="add-item-form" action="{{ route('shopping_lists.items.store', $listId) }}" method="POST" class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                @csrf
                <input type="text" name="name" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-shadow" placeholder="Afegir ítem..." required>
                <input type="text" name="tag" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-shadow" placeholder="Etiqueta (ex. Bonpreu-Esclat)">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg hover:shadow-md transition-all duration-300">
                    <i class="fas fa-plus mr-2"></i> Afegir Ítem
                </button>
            </form>
        </div>

        <!-- Categories i ítems -->
        @if (empty($categories))
            <p class="text-gray-600 text-center fade-in">Aquesta llista no té cap categoria ni ítem. Afegeix-ne un!</p>
        @else
            <div class="space-y-6">
                @foreach ($categories as $categoryId => $category)
                <div class="bg-gray-50 p-6 rounded-lg shadow-md transform transition-all duration-300 hover:shadow-lg fade-in">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center">
                            <span class="text-2xl font-semibold text-gray-800 editable-category cursor-text" data-category-id="{{ $categoryId }}">
                                {{ $category['name'] }}
                            </span>
                            <i class="fas fa-edit ml-2 text-blue-500 hover:text-blue-700 cursor-pointer edit-icon" onclick="editCategory(this)"></i>
                        </div>
                        <form action="{{ route('shopping_lists.categories.destroy', [$listId, $categoryId]) }}" method="POST" onsubmit="return confirm('Segur que vols eliminar aquesta categoria?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
                                <i class="fas fa-trash mr-2"></i> Eliminar Categoria
                            </button>
                        </form>
                    </div>
                    <ul id="sortable-{{ $categoryId }}" class="space-y-3">
                        @foreach ($items[$categoryId] ?? [] as $itemId => $item)
                        <li data-item-id="{{ $itemId }}" class="flex items-center justify-between bg-white p-4 rounded-lg shadow-sm item-complete {{ $item['is_completed'] ? 'opacity-75' : '' }} cursor-move">
                            <div class="flex items-center flex-grow">
                                <form class="update-completed-form" action="{{ route('shopping_lists.items.update', [$listId, $itemId]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="category_id" value="{{ $categoryId }}">
                                    <input type="checkbox" name="is_completed" value="1" {{ $item['is_completed'] ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-blue-400 mr-3">
                                </form>
                                <span class="{{ $item['is_completed'] ? 'line-through text-gray-500' : 'text-gray-800' }} editable-name mr-2 cursor-text" data-field="name" data-category-id="{{ $categoryId }}" data-item-id="{{ $itemId }}">
                                    {{ $item['name'] }}
                                </span>
                                <i class="fas fa-edit text-blue-500 hover:text-blue-700 mr-2 cursor-pointer edit-icon" onclick="editInline(this)"></i>
                                <span class="{{ $item['is_completed'] ? 'line-through text-gray-500' : 'text-gray-800' }} editable-tag cursor-text" data-field="tag" data-category-id="{{ $categoryId }}" data-item-id="{{ $itemId }}">
                                    {{ $item['tag'] ?? '' ? "({$item['tag']})" : '' }}
                                </span>
                                @if ($item['tag'] ?? '')
                                    <i class="fas fa-edit text-blue-500 hover:text-blue-700 cursor-pointer edit-icon" onclick="editInline(this)"></i>
                                @endif
                            </div>
                            <form class="delete-item-form" action="{{ route('shopping_lists.items.destroy', [$listId, $itemId]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="category_id" value="{{ $categoryId }}">
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        @endif

        
    </div>

    <!-- Template per nou ítem -->
    <template id="new-item-template">
        <li data-item-id="ITEM_ID" class="flex items-center justify-between bg-white p-4 rounded-lg shadow-sm cursor-move">
            <div class="flex items-center flex-grow">
                <form class="update-completed-form" action="{{ route('shopping_lists.items.update', [$listId, 'ITEM_ID']) }}" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="PATCH">
                    <input type="hidden" name="category_id" value="CATEGORY_ID">
                    <input type="checkbox" name="is_completed" value="1" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-400 mr-3">
                </form>
                <span class="text-gray-800 editable-name mr-2 cursor-text" data-field="name" data-category-id="CATEGORY_ID" data-item-id="ITEM_ID">
                    ITEM_NAME
                </span>
                <i class="fas fa-edit text-blue-500 hover:text-blue-700 mr-2 cursor-pointer edit-icon" onclick="editInline(this)"></i>
                <span class="text-gray-800 editable-tag cursor-text" data-field="tag" data-category-id="CATEGORY_ID" data-item-id="ITEM_ID">
                    ITEM_TAG
                </span>
                TAG_EDIT_ICON
            </div>
            <form class="delete-item-form" action="{{ route('shopping_lists.items.destroy', [$listId, 'ITEM_ID']) }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="category_id" value="CATEGORY_ID">
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </li>
    </template>

    <!-- Script per inicialitzar Sortable.js -->
    <script>
        document.querySelectorAll('[id^="sortable-"]').forEach(function(el) {
            new Sortable(el, {
                group: 'items',  // Permet drag entre diferents llistes (categories)
                animation: 150,
                ghostClass: 'bg-blue-100', // Classe per l'element fantasma durant drag
                handle: '.cursor-move', // Només draggable des de l'element sencer
                onEnd: function(evt) {
                    if (evt.from === evt.to) {
                        // Reorder dins la mateixa categoria
                        let categoryId = evt.to.id.split('-')[1];
                        let order = Array.from(evt.to.children).map(li => li.dataset.itemId);

                        fetch('{{ route("shopping_lists.items.reorder", $listId) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                category_id: categoryId,
                                order: order
                            })
                        }).then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  Toastify({
                                      text: "Ordre actualitzat!",
                                      duration: 3000,
                                      gravity: "top",
                                      position: "right",
                                      style: { background: "linear-gradient(to right, #00b09b, #96c93d)" }
                                  }).showToast();
                              }
                          }).catch(error => {
                              console.error('Error en reorder:', error);
                              Toastify({
                                  text: "Error en l'ordre!",
                                  duration: 3000,
                                  gravity: "top",
                                  position: "right",
                                  style: { background: "linear-gradient(to right, #ff5f6d, #ffc371)" }
                              }).showToast();
                          });
                    } else {
                        // Move a una altra categoria
                        let oldCat = evt.from.id.split('-')[1];
                        let newCat = evt.to.id.split('-')[1];
                        let itemId = evt.item.dataset.itemId;
                        let oldIndex = evt.oldIndex;
                        let newIndex = evt.newIndex;

                        fetch('{{ route("shopping_lists.items.move", $listId) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                item_id: itemId,
                                old_category_id: oldCat,
                                new_category_id: newCat,
                                old_index: oldIndex,
                                new_index: newIndex
                            })
                        }).then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  Toastify({
                                      text: "Ítem mogut!",
                                      duration: 3000,
                                      gravity: "top",
                                      position: "right",
                                      style: { background: "linear-gradient(to right, #00b09b, #96c93d)" }
                                  }).showToast();
                              }
                          }).catch(error => {
                              console.error('Error en move:', error);
                              Toastify({
                                  text: "Error en el moviment!",
                                  duration: 3000,
                                  gravity: "top",
                                  position: "right",
                                  style: { background: "linear-gradient(to right, #ff5f6d, #ffc371)" }
                              }).showToast();
                              location.reload();
                          });
                    }
                }
            });
        });

        // Replicació de productCategories en JS per determinar categoria en add AJAX
        const productCategories = {
            'llet': 'Làctics',
            'formatge': 'Làctics',
            'iogurt': 'Làctics',
            'mantega': 'Làctics',
            'nata': 'Làctics',
            'kefir': 'Làctics',
            'pa': 'Forn',
            'croissant': 'Forn',
            'baguet': 'Forn',
            'ensaimada': 'Forn',
            'magdalena': 'Forn',
            'poma': 'Fruites',
            'plàtan': 'Fruites',
            'taronja': 'Fruites',
            'maduixa': 'Fruites',
            'mango': 'Fruites',
            'pinya': 'Fruites',
            'patates': 'Verdures',
            'ceba': 'Verdures',
            'tomàquet': 'Verdures',
            'enciam': 'Verdures',
            'carbassó': 'Verdures',
            'albergínia': 'Verdures',
            'pollastre': 'Carns i peixos',
            'porc': 'Carns i peixos',
            'vedella': 'Carns i peixos',
            'salmon': 'Carns i peixos',
            'bacallà': 'Carns i peixos',
            'pizza': 'Congelats',
            'gelat': 'Congelats',
            'croquetes': 'Congelats',
            'verdures congelades': 'Congelats',
            'aigua': 'Begudes',
            'coca-cola': 'Begudes',
            'suc': 'Begudes',
            'cervesa': 'Begudes',
            'detergent': 'Neteja',
            'lleixiu': 'Neteja',
            'netejavidres': 'Neteja',
            'sabó': 'Neteja',
            'tonyina': 'Conserves',
            'tomàquet triturat': 'Conserves',
            'cigrons': 'Conserves',
            'mongetes': 'Conserves',
            'patates xip': 'Snacks',
            'avellanes': 'Snacks',
            'galetes': 'Snacks',
            'xocolata': 'Snacks',
        };

        // Manejar add item amb AJAX
        document.getElementById('add-item-form').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      // Determinar categoria en JS
                      let itemName = formData.get('name').toLowerCase();
                      let categoryName = productCategories[itemName] || 'Altres';
                      let categoryUl = document.querySelector(`ul[id^="sortable-"][id$="${data.category_id}"]`);
                      if (categoryUl) {
                          // Crear nou <li> des del template
                          let template = document.getElementById('new-item-template').innerHTML;
                          let tagHtml = data.item.tag ? `(${data.item.tag})` : '';
                          let tagEditIcon = data.item.tag ? '<i class="fas fa-edit text-blue-500 hover:text-blue-700 cursor-pointer edit-icon" onclick="editInline(this)"></i>' : '';
                          let newLiHtml = template.replace(/ITEM_ID/g, data.item_id)
                                                  .replace(/CATEGORY_ID/g, data.category_id)
                                                  .replace('ITEM_NAME', data.item.name)
                                                  .replace('ITEM_TAG', tagHtml)
                                                  .replace('TAG_EDIT_ICON', tagEditIcon);
                          let tempDiv = document.createElement('div');
                          tempDiv.innerHTML = newLiHtml;
                          let newLiElement = tempDiv.firstElementChild;
                          categoryUl.appendChild(newLiElement);
                          // Attach event to new delete form
                          newLiElement.querySelector('.delete-item-form').addEventListener('submit', deleteHandler);
                          // Reset form
                          this.reset();
                          Toastify({
                              text: "Ítem afegit!",
                              duration: 3000,
                              gravity: "top",
                              position: "right",
                              style: { background: "linear-gradient(to right, #00b09b, #96c93d)" }
                          }).showToast();
                      } else {
                          location.reload(); // Si nova categoria, reload per mostrar-la
                      }
                  }
              }).catch(error => {
                  console.error('Error en add:', error);
                  Toastify({
                      text: "Error afegint ítem!",
                      duration: 3000,
                      gravity: "top",
                      position: "right",
                      style: { background: "linear-gradient(to right, #ff5f6d, #ffc371)" }
                  }).showToast();
              });
        });

        // Handler per delete
        let deleteHandler = function(e) {
            e.preventDefault();
            if (confirm('Segur que vols eliminar aquest ítem?')) {
                let formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST', // Laravel usa POST per _method=DELETE
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          this.closest('li').remove();
                          Toastify({
                              text: "Ítem eliminat!",
                              duration: 3000,
                              gravity: "top",
                              position: "right",
                              style: { background: "linear-gradient(to right, #00b09b, #96c93d)" }
                          }).showToast();
                      }
                  }).catch(error => {
                      console.error('Error en delete:', error);
                      Toastify({
                          text: "Error eliminant ítem!",
                          duration: 3000,
                          gravity: "top",
                          position: "right",
                          style: { background: "linear-gradient(to right, #ff5f6d, #ffc371)" }
                      }).showToast();
                  });
            }
        };

        // Attach a tots els delete forms inicials
        document.querySelectorAll('.delete-item-form').forEach(form => {
            form.addEventListener('submit', deleteHandler);
        });

        // Funció per edición inline d'ítems (name o tag)
        function editInline(icon) {
            let span = icon.previousElementSibling; // El span editable anterior
            let field = span.dataset.field;
            let original = span.textContent.trim().replace(/^\(|\)$/g, ''); // Elimina parèntesis per tag
            let input = document.createElement('input');
            input.type = 'text';
            input.value = original;
            input.classList.add('p-1', 'border', 'rounded', 'focus:ring-2', 'focus:ring-blue-400', 'w-full');
            span.replaceWith(input);
            icon.style.display = 'none'; // Amaga icono durant edició
            input.focus();

            // Guardar amb Enter o blur
            const saveEdit = function() {
                let newValue = input.value.trim();
                let categoryId = input.closest('li').querySelector('input[name="category_id"]').value;
                let itemId = input.closest('li').dataset.itemId;
                let data = { category_id: categoryId };
                data[field] = newValue;

                fetch('{{ route("shopping_lists.items.update", [$listId, "ITEM_ID"]) }}'.replace('ITEM_ID', itemId), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          let newSpan = document.createElement('span');
                          newSpan.classList.add(field === 'name' ? 'editable-name' : 'editable-tag');
                          newSpan.dataset.field = field;
                          newSpan.dataset.categoryId = categoryId;
                          newSpan.dataset.itemId = itemId;
                          newSpan.textContent = field === 'tag' && newValue ? `(${newValue})` : newValue;
                          newSpan.classList.add('mr-2', 'cursor-text'); // Estils
                          input.replaceWith(newSpan);
                          // Si era tag i ara buit, no mostris icono (comprova si hi ha text)
                          icon.style.display = (field === 'tag' && !newValue) ? 'none' : 'inline';
                          Toastify({
                              text: "Ítem editat!",
                              duration: 3000,
                              gravity: "top",
                              position: "right",
                              style: { background: "linear-gradient(to right, #00b09b, #96c93d)" }
                          }).showToast();
                      }
                  }).catch(error => {
                      console.error('Error en update:', error);
                      input.value = original; // Revertir si error
                      Toastify({
                          text: "Error en l'edició!",
                          duration: 3000,
                          gravity: "top",
                          position: "right",
                          style: { background: "linear-gradient(to right, #ff5f6d, #ffc371)" }
                      }).showToast();
                  });
            };

            input.addEventListener('blur', saveEdit);
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    input.blur(); // Simula blur per guardar
                }
            });
        }

        // Funció per edición inline de categoria (name)
        function editCategory(icon) {
            let span = icon.previousElementSibling; // El span editable anterior
            let original = span.textContent.trim();
            let input = document.createElement('input');
            input.type = 'text';
            input.value = original;
            input.classList.add('p-1', 'border', 'rounded', 'focus:ring-2', 'focus:ring-blue-400', 'w-full', 'text-2xl', 'font-semibold');
            span.replaceWith(input);
            icon.style.display = 'none'; // Amaga icono durant edició
            input.focus();

            // Guardar amb Enter o blur
            const saveEdit = function() {
                let newValue = input.value.trim();
                let categoryId = span.dataset.categoryId;

                fetch('{{ route("shopping_lists.categories.update", [$listId, "CATEGORY_ID"]) }}'.replace('CATEGORY_ID', categoryId), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: newValue })
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          let newSpan = document.createElement('span');
                          newSpan.classList.add('editable-category');
                          newSpan.dataset.categoryId = categoryId;
                          newSpan.textContent = newValue;
                          newSpan.classList.add('text-2xl', 'font-semibold', 'text-gray-800', 'cursor-text');
                          input.replaceWith(newSpan);
                          icon.style.display = 'inline'; // Mostra icono
                          Toastify({
                              text: "Categoria editada!",
                              duration: 3000,
                              gravity: "top",
                              position: "right",
                              style: { background: "linear-gradient(to right, #00b09b, #96c93d)" }
                          }).showToast();
                      }
                  }).catch(error => {
                      console.error('Error en update category:', error);
                      input.value = original; // Revertir si error
                      Toastify({
                          text: "Error en l'edició de categoria!",
                          duration: 3000,
                          gravity: "top",
                          position: "right",
                          style: { background: "linear-gradient(to right, #ff5f6d, #ffc371)" }
                      }).showToast();
                  });
            };

            input.addEventListener('blur', saveEdit);
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    input.blur(); // Simula blur per guardar
                }
            });
        }

        // Handler per update completed amb AJAX
        document.querySelectorAll('.update-completed-form').forEach(form => {
            form.querySelector('input[type="checkbox"]').addEventListener('change', function(e) {
                let formData = new FormData(form);
                formData.set('is_completed', this.checked ? '1' : '0');
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          let li = this.closest('li');
                          let span = li.querySelectorAll('span');
                          span.forEach(s => {
                              s.classList.toggle('line-through', this.checked);
                              s.classList.toggle('text-gray-500', this.checked);
                              s.classList.toggle('text-gray-800', !this.checked);
                          });
                          li.classList.toggle('opacity-75', this.checked);
                          Toastify({
                              text: this.checked ? "Ítem completat!" : "Ítem pendent!",
                              duration: 3000,
                              gravity: "top",
                              position: "right",
                              style: { background: "linear-gradient(to right, #00b09b, #96c93d)" }
                          }).showToast();
                      }
                  }).catch(error => {
                      console.error('Error en update completed:', error);
                      this.checked = !this.checked; // Revertir checkbox si error
                      Toastify({
                          text: "Error actualitzant ítem!",
                          duration: 3000,
                          gravity: "top",
                          position: "right",
                          style: { background: "linear-gradient(to right, #ff5f6d, #ffc371)" }
                      }).showToast();
                  });
            });
        });
    </script>
</body>
</html>