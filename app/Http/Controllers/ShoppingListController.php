<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

class ShoppingListController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    // Generar una clau única per compartir
    private function generateUniqueShareCode()
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        if ($this->firebase->get("share_codes/$code")) {
            return $this->generateUniqueShareCode();
        }

        return $code;
    }

    // Mostrar totes les llistes de la compra de l'usuari
    public function index()
    {
        $userId = auth()->id();
        $createdLists = $this->firebase->get("shopping_lists/created/$userId") ?? [];
        $sharedLists = $this->firebase->get("shopping_lists/shared/$userId") ?? [];

        // Normalitzar llistes per assegurar que totes tenen 'name'
        $shoppingLists = [];

        // Afegir llistes creades
        foreach ($createdLists as $listId => $list) {
            $shoppingLists[$listId] = [
                'list_id' => $listId,
                'name' => $list['name'] ?? 'Llista sense nom',
                'share_code' => $list['share_code'] ?? null,
                'created_at' => $list['created_at'] ?? null,
                'is_owner' => true,
            ];
        }

        // Afegir llistes compartides
        foreach ($sharedLists as $listId => $list) {
            // Evitar sobreescriure llistes creades
            if (!isset($shoppingLists[$listId])) {
                // Obtenir el share_code i user_id des de share_codes
                $shareCodeData = $this->firebase->get("share_codes");
                $ownerId = null;
                foreach ($shareCodeData as $code => $codeData) {
                    if (isset($codeData[$listId]['list_id']) && $codeData[$listId]['list_id'] === $listId) {
                        $ownerId = $codeData[$listId]['user_id'];
                        break;
                    }
                }

                if ($ownerId) {
                    // Obtenir la llista original per recuperar el 'name'
                    $originalList = $this->firebase->get("shopping_lists/created/$ownerId/$listId");
                    $shoppingLists[$listId] = [
                        'list_id' => $listId,
                        'name' => $originalList['name'] ?? 'Llista sense nom',
                        'share_code' => $originalList['share_code'] ?? null,
                        'created_at' => $list['added_at'] ?? null,
                        'is_owner' => false,
                    ];
                } else {
                    // Si no es troba ownerId, afegir amb dades mínimes
                    $shoppingLists[$listId] = [
                        'list_id' => $listId,
                        'name' => 'Llista sense nom',
                        'share_code' => null,
                        'created_at' => $list['added_at'] ?? null,
                        'is_owner' => false,
                    ];
                }
            }
        }

        return view('shopping_lists.index', compact('shoppingLists'));
    }

    // Crear una nova llista de la compra
    public function create()
    {
        return view('shopping_lists.create');
    }

    // Emmagatzemar una nova llista de la compra
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $userId = auth()->id();
        $listId = uniqid();
        $shareCode = $this->generateUniqueShareCode();
        $data = [
            'name' => $request->name,
            'user_id' => $userId,
            'share_code' => $shareCode,
            'created_at' => now()->toIso8601String(),
        ];

        // Guardar llista i clau
        $this->firebase->set("shopping_lists/created/$userId/$listId", $data);
        $this->firebase->set("share_codes/$shareCode/$listId", [
            'user_id' => $userId,
            'list_id' => $listId,
        ]);

        return redirect()->route('shopping_lists.index')->with('success', 'Llista creada correctament');
    }

    // Mostrar una llista de la compra específica
    public function show($listId)
    {
        $userId = auth()->id();
        $createdList = $this->firebase->get("shopping_lists/created/$userId/$listId");
        $sharedList = $this->firebase->get("shopping_lists/shared/$userId/$listId");

        if (!$createdList && !$sharedList) {
            abort(404, 'Llista no trobada');
        }

        // Normalitzar la llista
        if ($createdList) {
            // Llista creada per l'usuari
            $shoppingList = [
                'list_id' => $listId,
                'name' => $createdList['name'] ?? 'Llista sense nom',
                'share_code' => $createdList['share_code'] ?? null,
                'user_id' => $createdList['user_id'] ?? $userId,
                'created_at' => $createdList['created_at'] ?? null,
                'is_owner' => true,
            ];
        } else {
            // Llista compartida: obtenir user_id des de share_codes
            $shareCodeData = $this->firebase->get("share_codes");
            $ownerId = null;
            foreach ($shareCodeData as $code => $codeData) {
                if (isset($codeData[$listId]['list_id']) && $codeData[$listId]['list_id'] === $listId) {
                    $ownerId = $codeData[$listId]['user_id'];
                    break;
                }
            }

            if (!$ownerId) {
                abort(404, 'Propietari de la llista no trobat');
            }

            // Obtenir la llista original
            $originalList = $this->firebase->get("shopping_lists/created/$ownerId/$listId");
            if (!$originalList) {
                abort(404, 'Llista original no trobada');
            }

            $shoppingList = [
                'list_id' => $listId,
                'name' => $originalList['name'] ?? 'Llista sense nom',
                'share_code' => $originalList['share_code'] ?? null,
                'user_id' => $ownerId,
                'created_at' => $sharedList['added_at'] ?? null,
                'is_owner' => false,
            ];
        }

        $categories = $this->firebase->get("categories/$listId") ?? [];
        $items = [];
        foreach ($categories as $categoryId => $category) {
            $catItems = $this->firebase->get("items/$listId/$categoryId") ?? [];
            // Ordenar els ítems per 'order' (tractant null com 0)
            uasort($catItems, function($a, $b) {
                return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
            });

            // Reassignar orders seqüencials (0 a n-1) i actualitzar Firebase si cal
            $order = 0;
            foreach ($catItems as $id => &$item) {
                $currentOrder = $item['order'] ?? 0;
                if ($currentOrder !== $order) {
                    $item['order'] = $order;
                    $this->firebase->update("items/$listId/$categoryId/$id", [
                        'order' => $order,
                        'updated_at' => now()->toIso8601String(),
                    ]);
                }
                $order++;
            }

            $items[$categoryId] = $catItems;
        }

        return view('shopping_lists.show', compact('shoppingList', 'categories', 'items', 'listId'));
    }

    // Editar una llista de la compra
    public function edit($listId)
    {
        $userId = auth()->id();
        $shoppingList = $this->firebase->get("shopping_lists/created/$userId/$listId") ??
                       $this->firebase->get("shopping_lists/shared/$userId/$listId");

        if (!$shoppingList) {
            abort(404, 'Llista no trobada');
        }

        return view('shopping_lists.edit', compact('shoppingList', 'listId'));
    }

    // Actualitzar una llista de la compra
    public function update(Request $request, $listId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $userId = auth()->id();
        $path = $this->firebase->get("shopping_lists/created/$userId/$listId") 
                ? "shopping_lists/created/$userId/$listId"
                : "shopping_lists/shared/$userId/$listId";

        $this->firebase->update($path, [
            'name' => $request->name,
            'updated_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('shopping_lists.index')->with('success', 'Llista actualitzada correctament');
    }

    // Esborrar una llista de la compra
    public function destroy($listId)
    {
        $userId = auth()->id();
        $list = $this->firebase->get("shopping_lists/created/$userId/$listId") ??
                $this->firebase->get("shopping_lists/shared/$userId/$listId");

        if (!$list) {
            abort(404, 'Llista no trobada');
        }

        $path = $list['user_id'] == $userId ? "shopping_lists/created/$userId/$listId" : "shopping_lists/shared/$userId/$listId";
        if ($list['user_id'] == $userId) {
            // Eliminar clau de compartir
            $this->firebase->delete("share_codes/{$list['share_code']}/$listId");
        }
        $this->firebase->delete($path);

        return redirect()->route('shopping_lists.index')->with('success', 'Llista eliminada correctament');
    }

    // Unir-se a una llista amb una clau
    public function join(Request $request)
    {
        $request->validate(
            [
                'share_code' => ['required', 'size:6'],
            ],
            [
                'share_code.size' => 'La clau ha de tenir exactament 6 caràcters.',
            ]
        );

        $userId = auth()->id();
        $shareCode = $request->input('share_code');

        // Obtenir les dades de share_codes/$shareCode
        $shareCodeData = $this->firebase->get("share_codes/$shareCode");

        if (!$shareCodeData) {
            return back()->withErrors(['share_code' => 'Clau no vàlida']);
        }

        // Com share_codes/$shareCode conté un nivell amb $listId, agafem la primera entrada
        $listData = reset($shareCodeData); // Obté el primer element de l'array
        if (!$listData || !isset($listData['list_id'], $listData['user_id'])) {
            return back()->withErrors(['share_code' => 'Clau no vàlida']);
        }

        $listId = $listData['list_id'];
        $ownerId = $listData['user_id'];

        if ($userId == $ownerId || $this->firebase->get("shopping_lists/shared/$userId/$listId")) {
            return back()->withErrors(['share_code' => "Ja formes part d'aquesta llista"]);
        }

        $this->firebase->set("shopping_lists/shared/$userId/$listId", [
            'list_id' => $listId,
            'added_at' => now()->toDateTimeString(),
        ]);

        return redirect()->route('shopping_lists.index')->with('status', "Ara formes part d'aquesta llista");
    }

    // Emmagatzemar un nou ítem en una llista
    public function storeItem(Request $request, $listId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tag' => 'nullable|string|max:255',
        ]);

        $userId = auth()->id();
        $list = $this->firebase->get("shopping_lists/created/$userId/$listId") ??
                $this->firebase->get("shopping_lists/shared/$userId/$listId");

        if (!$list) {
            abort(404, 'Llista no trobada');
        }

        $productCategories = [
            // Làctics
            'llet' => 'Làctics',
            'formatge' => 'Làctics',
            'iogurt' => 'Làctics',
            'mantega' => 'Làctics',
            'nata' => 'Làctics',
            'kefir' => 'Làctics',

            // Forn
            'pa' => 'Forn',
            'croissant' => 'Forn',
            'baguet' => 'Forn',
            'ensaimada' => 'Forn',
            'magdalena' => 'Forn',

            // Fruites
            'poma' => 'Fruites',
            'plàtan' => 'Fruites',
            'taronja' => 'Fruites',
            'maduixa' => 'Fruites',
            'mango' => 'Fruites',
            'pinya' => 'Fruites',

            // Verdures
            'patates' => 'Verdures',
            'ceba' => 'Verdures',
            'tomàquet' => 'Verdures',
            'enciam' => 'Verdures',
            'carbassó' => 'Verdures',
            'albergínia' => 'Verdures',

            // Carns i peixos
            'pollastre' => 'Carns i peixos',
            'porc' => 'Carns i peixos',
            'vedella' => 'Carns i peixos',
            'salmon' => 'Carns i peixos',
            'bacallà' => 'Carns i peixos',

            // Congelats
            'pizza' => 'Congelats',
            'gelat' => 'Congelats',
            'croquetes' => 'Congelats',
            'verdures congelades' => 'Congelats',

            // Begudes
            'aigua' => 'Begudes',
            'coca-cola' => 'Begudes',
            'suc' => 'Begudes',
            'cervesa' => 'Begudes',

            // Neteja
            'detergent' => 'Neteja',
            'lleixiu' => 'Neteja',
            'netejavidres' => 'Neteja',
            'sabó' => 'Neteja',

            // Conserves
            'tonyina' => 'Conserves',
            'tomàquet triturat' => 'Conserves',
            'cigrons' => 'Conserves',
            'mongetes' => 'Conserves',

            // Snacks
            'patates xip' => 'Snacks',
            'avellanes' => 'Snacks',
            'galetes' => 'Snacks',
            'xocolata' => 'Snacks',
        ];

        $itemName = strtolower($request->name);
        $categoryName = $productCategories[$itemName] ?? 'Altres';

        $categories = $this->firebase->get("categories/$listId") ?? [];
        $categoryId = null;
        foreach ($categories as $id => $cat) {
            if ($cat['name'] === $categoryName) {
                $categoryId = $id;
                break;
            }
        }

        if (!$categoryId) {
            $categoryId = uniqid();
            $this->firebase->set("categories/$listId/$categoryId", [
                'name' => $categoryName,
                'created_at' => now()->toIso8601String(),
            ]);
        }

        // Obtenir ítems actuals per calcular l'order (últim +1)
        $currentItems = $this->firebase->get("items/$listId/$categoryId") ?? [];
        $nextOrder = count($currentItems);

        $itemId = uniqid();
        $this->firebase->set("items/$listId/$categoryId/$itemId", [
            'name' => $request->name,
            'is_completed' => false,
            'tag' => $request->tag ?? '',
            'order' => $nextOrder,  // Nou camp order
            'created_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('shopping_lists.show', $listId)->with('success', 'Ítem afegit correctament');
    }

    // Actualitzar un ítem (marcar com completat, o editar name/tag)
    public function updateItem(Request $request, $listId, $itemId)
    {
        $request->validate([
            'category_id' => 'required|string',
        ]);

        $userId = auth()->id();
        $list = $this->firebase->get("shopping_lists/created/$userId/$listId") ??
                $this->firebase->get("shopping_lists/shared/$userId/$listId");

        if (!$list) {
            abort(404, 'Llista no trobada');
        }

        $categoryId = $request->category_id;
        $updateData = [
            'updated_at' => now()->toIso8601String(),
        ];

        if ($request->has('is_completed')) {
            $updateData['is_completed'] = true;
        } else {
            $updateData['is_completed'] = false; // Si no, desmarca (per checkboxes)
        }

        // Afegir name o tag si s'envien (per edición inline)
        if ($request->has('name')) {
            $request->validate(['name' => 'required|string|max:255']);
            $updateData['name'] = $request->input('name');
        }

        if ($request->has('tag')) {
            $request->validate(['tag' => 'nullable|string|max:255']);
            $updateData['tag'] = $request->input('tag');
        }

        $this->firebase->update("items/$listId/$categoryId/$itemId", $updateData);

        // Retornar JSON per AJAX, o redirect per form normal
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('shopping_lists.show', $listId)->with('success', 'Ítem actualitzat correctament');
    }

    // Nou mètode per actualitzar una categoria (nom)
    public function updateCategory(Request $request, $listId, $categoryId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $userId = auth()->id();
        $list = $this->firebase->get("shopping_lists/created/$userId/$listId") ??
                $this->firebase->get("shopping_lists/shared/$userId/$listId");

        if (!$list) {
            return response()->json(['error' => 'Llista no trobada'], 404);
        }

        $updateData = [
            'name' => $request->input('name'),
            'updated_at' => now()->toIso8601String(),
        ];

        $this->firebase->update("categories/$listId/$categoryId", $updateData);

        return response()->json(['success' => true]);
    }

    // Eliminar un ítem
    public function destroyItem(Request $request, $listId, $itemId)
    {
        $request->validate([
            'category_id' => 'required',
        ]);

        $userId = auth()->id();
        $list = $this->firebase->get("shopping_lists/created/$userId/$listId") ??
                $this->firebase->get("shopping_lists/shared/$userId/$listId");

        if (!$list) {
            abort(404, 'Llista no trobada');
        }

        $categoryId = $request->category_id;
        $this->firebase->delete("items/$listId/$categoryId/$itemId");

        return redirect()->route('shopping_lists.show', $listId)->with('success', 'Ítem eliminat correctament');
    }

    // Eliminar una categoria
    public function destroyCategory($listId, $categoryId)
    {
        $userId = auth()->id();
        $list = $this->firebase->get("shopping_lists/created/$userId/$listId") ??
                $this->firebase->get("shopping_lists/shared/$userId/$listId");

        if (!$list) {
            abort(404, 'Llista no trobada');
        }

        $this->firebase->delete("categories/$listId/$categoryId");
        $this->firebase->delete("items/$listId/$categoryId");

        return redirect()->route('shopping_lists.show', $listId)->with('success', 'Categoria eliminada correctament');
    }

    // Mètode per reordenar ítems dins la mateixa categoria
    public function reorder(Request $request, $listId)
    {
        $request->validate([
            'category_id' => 'required|string',
            'order' => 'required|array',
        ]);

        $userId = auth()->id();
        $list = $this->firebase->get("shopping_lists/created/$userId/$listId") ??
                $this->firebase->get("shopping_lists/shared/$userId/$listId");

        if (!$list) {
            return response()->json(['error' => 'Llista no trobada'], 404);
        }

        $categoryId = $request->input('category_id');
        $order = $request->input('order');

        foreach ($order as $index => $itemId) {
            $this->firebase->update("items/$listId/$categoryId/$itemId", [
                'order' => $index,
                'updated_at' => now()->toIso8601String(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    // Mètode per moure un ítem entre categories i ajustar orders
    public function moveItem(Request $request, $listId)
    {
        $request->validate([
            'item_id' => 'required|string',
            'old_category_id' => 'required|string',
            'new_category_id' => 'required|string',
            'old_index' => 'required|integer',
            'new_index' => 'required|integer',
        ]);

        $userId = auth()->id();
        $list = $this->firebase->get("shopping_lists/created/$userId/$listId") ??
                $this->firebase->get("shopping_lists/shared/$userId/$listId");

        if (!$list) {
            return response()->json(['error' => 'Llista no trobada'], 404);
        }

        $itemId = $request->input('item_id');
        $oldCat = $request->input('old_category_id');
        $newCat = $request->input('new_category_id');
        $oldIndex = $request->input('old_index');
        $newIndex = $request->input('new_index');

        $itemPath = "items/$listId/$oldCat/$itemId";
        $item = $this->firebase->get($itemPath);

        if (!$item) {
            return response()->json(['error' => 'Ítem no trobat'], 404);
        }

        // Eliminar de la categoria vella
        $this->firebase->delete($itemPath);

        // Assignar nou order i guardar a la nova categoria
        $item['order'] = $newIndex;
        $item['updated_at'] = now()->toIso8601String();
        $this->firebase->set("items/$listId/$newCat/$itemId", $item);

        // Ajustar orders a la categoria vella (decrementar els > old_index)
        $oldItems = $this->firebase->get("items/$listId/$oldCat") ?? [];
        foreach ($oldItems as $id => $it) {
            $currentOrder = $it['order'] ?? 0;
            if ($currentOrder > $oldIndex) {
                $this->firebase->update("items/$listId/$oldCat/$id", [
                    'order' => $currentOrder - 1,
                    'updated_at' => now()->toIso8601String(),
                ]);
            }
        }

        // Ajustar orders a la nova categoria (incrementar els >= new_index, excepte el nou ítem)
        $newItems = $this->firebase->get("items/$listId/$newCat") ?? [];
        foreach ($newItems as $id => $it) {
            if ($id !== $itemId) {
                $currentOrder = $it['order'] ?? 0;
                if ($currentOrder >= $newIndex) {
                    $this->firebase->update("items/$listId/$newCat/$id", [
                        'order' => $currentOrder + 1,
                        'updated_at' => now()->toIso8601String(),
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
    }
}