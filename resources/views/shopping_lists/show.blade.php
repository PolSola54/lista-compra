<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $shoppingList['name'] ?? 'Llista sense nom' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        .fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-primary:hover {
            background-color: #2563eb;
            box-shadow: 0 10px 15px rgba(0,0,0,0.2);
            transform: translateY(-1px);
        }
        .btn-gray {
            background-color: #6b7280;
            color: white;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-gray:hover {
            background-color: #4b5563;
            box-shadow: 0 10px 15px rgba(0,0,0,0.2);
        }
        .category-card {
            background-color: #f3f4f6;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .category-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            transform: translateY(-4px);
        }
        .item-card {
            background-color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: opacity 0.3s;
            position: relative;
        }
        .input-field {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }
        .input-field:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(96,165,250,0.3);
            border-color: transparent;
        }
        .text-strike {
            text-decoration: line-through;
            color: #6b7280;
        }
        .text-normal {
            color: #1f2937;
        }
        .delete-btn {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: opacity 0.2s;
            color: #ef4444;
        }
        .item-card:hover .delete-btn {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-200 min-h-screen p-4">

    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-2xl p-8">
        <h1 class="text-4xl font-bold mb-8 text-center text-gray-800">{{ $shoppingList['name'] ?? 'Llista sense nom' }}</h1>

        <!-- Botó per mostrar/amagar la clau -->
        <div class="mb-6 text-center">
            <button id="toggle-share-code" class="btn-primary">
                <i class="fas fa-share-alt mr-2"></i> Mostrar clau per compartir
            </button>
        </div>

        <!-- Clau per compartir (amagada per defecte) -->
        <div id="share-code-div" class="hidden mb-8 bg-blue-50 p-6 rounded-xl border border-blue-200 fade-in">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-gray-700 font-semibold text-lg">Clau per compartir:</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2 select-all">{{ $shoppingList['share_code'] }}</p>
                    <p class="text-sm text-gray-500 mt-3">Comparteix aquesta clau amb qui vulguis que s'uneixi.</p>
                </div>
                <button onclick="navigator.clipboard.writeText('{{ $shoppingList['share_code'] }}').then(() => Toastify({text: 'Clau copiada!', duration: 2500, style: {background: '#10b981'}}).showToast())"
                        class="btn-primary whitespace-nowrap">
                    <i class="fas fa-copy mr-2"></i> Copiar clau
                </button>
            </div>
        </div>

        <div class="mb-6 text-center">
            <a href="{{ route('shopping_lists.index') }}" class="btn-gray inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Tornar a les llistes
            </a>
        </div>

        <!-- Formulari per afegir ítem -->
        <div class="mb-10 fade-in">
            <form id="add-item-form" action="{{ route('shopping_lists.items.store', $listId) }}" method="POST" class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                @csrf
                <input type="text" name="name" class="input-field" placeholder="Afegir ítem..." required>
                <input type="text" name="tag" class="input-field" placeholder="Notes (ex. Bonpreu-Esclat, 2kg, etc.)">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i> Afegir
                </button>
            </form>
        </div>

        <!-- Categories i ítems -->
        <div id="categories-container" class="space-y-8">
            @if (empty($categories))
                <p class="text-center text-gray-600 text-xl fade-in">La llista està buida. Afegeix el primer ítem!</p>
            @else
                @foreach ($categories as $categoryId => $category)
                <div class="category-card">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center">
                            <span class="text-2xl font-bold text-gray-800 editable-category cursor-text" data-category-id="{{ $categoryId }}">
                                {{ $category['name'] }}
                            </span>
                            <i class="fas fa-edit ml-2 text-blue-500 hover:text-blue-700 cursor-pointer edit-icon" onclick="editCategory(this)"></i>
                        </div>
                        <form action="{{ route('shopping_lists.categories.destroy', [$listId, $categoryId]) }}" method="POST" onsubmit="return confirm('Segur que vols eliminar aquesta categoria i tots els seus ítems?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash text-xl"></i>
                            </button>
                        </form>
                    </div>
                    <ul id="sortable-{{ $categoryId }}" class="space-y-3">
                        @foreach ($items[$categoryId] ?? [] as $itemId => $item)
                        <li data-item-id="{{ $itemId }}" class="item-card {{ $item['is_completed'] ? 'opacity-75' : '' }}">
                            <div class="flex items-center pr-10">
                                <form class="update-completed-form" action="{{ route('shopping_lists.items.update', [$listId, $itemId]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="category_id" value="{{ $categoryId }}">
                                    <input type="checkbox" name="is_completed" value="1" {{ $item['is_completed'] ? 'checked' : '' }} class="w-6 h-6 text-blue-600 rounded focus:ring-blue-400">
                                </form>
                                <div class="ml-4 flex-1 flex items-center">
                                    <span class="{{ $item['is_completed'] ? 'text-strike' : 'text-normal' }} editable-name cursor-text mr-2 text-lg" data-field="name" data-category-id="{{ $categoryId }}" data-item-id="{{ $itemId }}">
                                        {{ $item['name'] }}
                                    </span>
                                    <i class="fas fa-edit text-blue-500 hover:text-blue-700 cursor-pointer edit-icon mr-3" onclick="editInline(this)"></i>
                                    <span class="{{ $item['is_completed'] ? 'text-strike' : 'text-normal' }} editable-tag cursor-text text-gray-600" data-field="tag" data-category-id="{{ $categoryId }}" data-item-id="{{ $itemId }}">
                                        {{ $item['tag'] ?? '' ? "({$item['tag']})" : '' }}
                                    </span>
                                    <i class="fas fa-edit text-blue-500 hover:text-blue-700 cursor-pointer edit-icon ml-1" onclick="editInline(this)"></i>
                                </div>
                            </div>
                            <button class="delete-btn text-red-500 hover:text-red-700">
                                <i class="fas fa-trash text-xl"></i>
                            </button>
                            <form class="delete-item-form hidden" action="{{ route('shopping_lists.items.destroy', [$listId, $itemId]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="category_id" value="{{ $categoryId }}">
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
        <li data-item-id="ITEM_ID" class="item-card cursor-move">
            <div class="flex items-center pr-10">
                <form class="update-completed-form" action="{{ route('shopping_lists.items.update', [$listId, 'ITEM_ID']) }}" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="PATCH">
                    <input type="hidden" name="category_id" value="CATEGORY_ID">
                    <input type="checkbox" name="is_completed" value="1" class="w-6 h-6 text-blue-600 rounded focus:ring-blue-400">
                </form>
                <div class="ml-4 flex-1 flex items-center">
                    <span class="text-normal editable-name cursor-text mr-2 text-lg" data-field="name" data-category-id="CATEGORY_ID" data-item-id="ITEM_ID">
                        ITEM_NAME
                    </span>
                    <i class="fas fa-edit text-blue-500 hover:text-blue-700 cursor-pointer edit-icon mr-3" onclick="editInline(this)"></i>
                    <span class="text-normal editable-tag cursor-text text-gray-600" data-field="tag" data-category-id="CATEGORY_ID" data-item-id="ITEM_ID">
                        ITEM_TAG
                    </span>
                    <i class="fas fa-edit text-blue-500 hover:text-blue-700 cursor-pointer edit-icon ml-1" onclick="editInline(this)"></i>
                </div>
            </div>
            <button class="delete-btn text-red-500 hover:text-red-700">
                <i class="fas fa-trash text-xl"></i>
            </button>
            <form class="delete-item-form hidden" action="{{ route('shopping_lists.items.destroy', [$listId, 'ITEM_ID']) }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="category_id" value="CATEGORY_ID">
            </form>
        </li>
    </template>

    <script>
        // Toggle clau de compartir
        document.getElementById('toggle-share-code').addEventListener('click', function() {
            let div = document.getElementById('share-code-div');
            div.classList.toggle('hidden');
            this.innerHTML = div.classList.contains('hidden') 
                ? '<i class="fas fa-share-alt mr-2"></i> Mostrar clau per compartir'
                : '<i class="fas fa-eye-slash mr-2"></i> Amagar clau';
        });

        // Inicialitzar Sortable.js
        document.querySelectorAll('[id^="sortable-"]').forEach(function(el) {
            new Sortable(el, {
                group: 'items',
                animation: 150,
                ghostClass: 'bg-blue-100',
                handle: '.cursor-move',
                onEnd: function(evt) {
                    if (evt.from === evt.to) {
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
                        }).then(r => r.json()).then(d => d.success && Toastify({text: "Ordre actualitzat!", duration: 3000, style: {background: "#10b981"}}).showToast());
                    } else {
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
                        }).then(r => r.json()).then(d => d.success && Toastify({text: "Ítem mogut!", duration: 3000, style: {background: "#10b981"}}).showToast());
                    }
                }
            });
        });

        // Replicació de productCategories en JS
        const productCategories = {
            'llet': 'Làctics','formatge': 'Làctics','iogurt': 'Làctics','mantega': 'Làctics','nata': 'Làctics','kefir': 'Làctics',
            'pa': 'Forn','croissant': 'Forn','baguet': 'Forn','ensaimada': 'Forn','magdalena': 'Forn',
            'poma': 'Fruites','plàtan': 'Fruites','taronja': 'Fruites','maduixa': 'Fruites','mango': 'Fruites','pinya': 'Fruites',
            'patates': 'Verdures','ceba': 'Verdures','tomàquet': 'Verdures','enciam': 'Verdures','carbassó': 'Verdures','albergínia': 'Verdures',
            'pollastre': 'Carns i peixos','porc': 'Carns i peixos','vedella': 'Carns i peixos','salmon': 'Carns i peixos','bacallà': 'Carns i peixos',
            'pizza': 'Congelats','gelat': 'Congelats','croquetes': 'Congelats','verdures congelades': 'Congelats',
            'aigua': 'Begudes','coca-cola': 'Begudes','suc': 'Begudes','cervesa': 'Begudes',
            'detergent': 'Neteja','lleixiu': 'Neteja','netejavidres': 'Neteja','sabó': 'Neteja',
            'tonyina': 'Conserves','tomàquet triturat': 'Conserves','cigrons': 'Conserves','mongetes': 'Conserves',
            'patates xip': 'Snacks','avellanes': 'Snacks','galetes': 'Snacks','xocolata': 'Snacks',
        };

        // Add item amb AJAX
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
                      let itemName = formData.get('name').toLowerCase();
                      let categoryName = productCategories[itemName] || 'Altres';
                      let categoryUl = document.querySelector(`ul[id^="sortable-"][id$="${data.category_id}"]`);
                      if (categoryUl) {
                          let template = document.getElementById('new-item-template').innerHTML;
                          let tagHtml = data.item.tag ? `(${data.item.tag})` : '';
                          let tagEditIcon = '<i class="fas fa-edit text-blue-500 hover:text-blue-700 cursor-pointer edit-icon" onclick="editInline(this)"></i>';
                          let newLiHtml = template.replace(/ITEM_ID/g, data.item_id)
                                                  .replace(/CATEGORY_ID/g, data.category_id)
                                                  .replace('ITEM_NAME', data.item.name)
                                                  .replace('ITEM_TAG', tagHtml)
                                                  .replace('TAG_EDIT_ICON', tagEditIcon);
                          let tempDiv = document.createElement('div');
                          tempDiv.innerHTML = newLiHtml;
                          let newLiElement = tempDiv.firstElementChild;
                          categoryUl.appendChild(newLiElement);
                          newLiElement.querySelector('.delete-item-form').addEventListener('submit', deleteHandler);
                          newLiElement.querySelector('.update-completed-form input[type="checkbox"]').addEventListener('change', completedHandler);
                          // Mou la categoria al top
                          let categoryDiv = categoryUl.closest('.category-card');
                          let container = document.getElementById('categories-container');
                          container.prepend(categoryDiv);
                          this.reset();
                          Toastify({text: "Ítem afegit!", duration: 3000, style: {background: "#10b981"}}).showToast();
                      } else {
                          location.reload();
                      }
                  }
              }).catch(error => {
                  console.error('Error en add:', error);
                  Toastify({text: "Error afegint ítem!", duration: 3000, style: {background: "#ef4444"}}).showToast();
              });
        });

        // Handler per delete
        let deleteHandler = function(e) {
            e.preventDefault();
            if (confirm('Segur que vols eliminar aquest ítem?')) {
                let formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        this.closest('li').remove();
                        Toastify({text: "Ítem eliminat!", duration: 3000, style: {background: "#10b981"}}).showToast();
                    }
                }).catch(err => {
                    console.error(err);
                    Toastify({text: "Error eliminant ítem!", duration: 3000, style: {background: "#ef4444"}}).showToast();
                });
            }
        };

        document.querySelectorAll('.delete-item-form').forEach(form => form.addEventListener('submit', deleteHandler));
        document.querySelectorAll('.item-card .delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Segur que vols eliminar aquest ítem?')) {
                    this.nextElementSibling.submit();
                }
            });
        });

        // Handler per completed checkbox
        let completedHandler = function(e) {
            let formData = new FormData(this.form);
            formData.set('is_completed', this.checked ? '1' : '0');
            fetch(this.form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    let li = this.closest('li');
                    let spans = li.querySelectorAll('span');
                    spans.forEach(s => {
                        s.classList.toggle('text-strike', this.checked);
                        s.classList.toggle('text-normal', !this.checked);
                    });
                    li.classList.toggle('opacity-75', this.checked);
                    Toastify({text: this.checked ? "Ítem completat!" : "Ítem pendent!", duration: 3000, style: {background: "#10b981"}}).showToast();
                }
            }).catch(err => {
                console.error(err);
                this.checked = !this.checked;
                Toastify({text: "Error actualitzant estat!", duration: 3000, style: {background: "#ef4444"}}).showToast();
            });
        };

        document.querySelectorAll('.update-completed-form input[type="checkbox"]').forEach(cb => cb.addEventListener('change', completedHandler));

        // Edició inline ítem
        function editInline(icon) {
            let span = icon.previousElementSibling;
            let field = span.dataset.field;
            let original = span.textContent.trim().replace(/^\(|\)$/g, '');
            let input = document.createElement('input');
            input.type = 'text';
            input.value = original;
            input.classList.add('p-1', 'border', 'rounded', 'focus:ring-2', 'focus:ring-blue-400', 'w-full');
            span.replaceWith(input);
            icon.style.display = 'none';
            input.focus();

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
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        let newSpan = document.createElement('span');
                        newSpan.classList.add(field === 'name' ? 'editable-name' : 'editable-tag');
                        newSpan.dataset.field = field;
                        newSpan.dataset.categoryId = categoryId;
                        newSpan.dataset.itemId = itemId;
                        newSpan.textContent = field === 'tag' && newValue ? `(${newValue})` : newValue;
                        newSpan.classList.add('mr-2', 'cursor-text');
                        input.replaceWith(newSpan);
                        icon.style.display = 'inline';
                        Toastify({text: "Ítem editat!", duration: 3000, style: {background: "#10b981"}}).showToast();
                    }
                }).catch(err => {
                    console.error(err);
                    input.value = original;
                    Toastify({text: "Error en l'edició!", duration: 3000, style: {background: "#ef4444"}}).showToast();
                });
            };

            input.addEventListener('blur', saveEdit);
            input.addEventListener('keydown', e => e.key === 'Enter' && input.blur());
        }

        // Edició inline categoria
        function editCategory(icon) {
            let span = icon.previousElementSibling;
            let original = span.textContent.trim();
            let input = document.createElement('input');
            input.type = 'text';
            input.value = original;
            input.classList.add('p-1', 'border', 'rounded', 'focus:ring-2', 'focus:ring-blue-400', 'w-full', 'text-2xl', 'font-semibold');
            span.replaceWith(input);
            icon.style.display = 'none';
            input.focus();

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
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        let newSpan = document.createElement('span');
                        newSpan.classList.add('editable-category');
                        newSpan.dataset.categoryId = categoryId;
                        newSpan.textContent = newValue;
                        newSpan.classList.add('text-2xl', 'font-semibold', 'text-gray-800', 'cursor-text');
                        input.replaceWith(newSpan);
                        icon.style.display = 'inline';
                        Toastify({text: "Categoria editada!", duration: 3000, style: {background: "#10b981"}}).showToast();
                    }
                }).catch(err => {
                    console.error(err);
                    input.value = original;
                    Toastify({text: "Error en l'edició de categoria!", duration: 3000, style: {background: "#ef4444"}}).showToast();
                });
            };

            input.addEventListener('blur', saveEdit);
            input.addEventListener('keydown', e => e.key === 'Enter' && input.blur());
        }
    </script>
</body>
</html>