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

    // Mostrar totes les llistes de la compra de l'usuari
    public function index()
    {
        $userId = auth()->id();
        $createdLists = $this->firebase->get("shopping_lists/created/$userId") ?? [];
        $sharedLists = $this->firebase->get("shopping_lists/shared/$userId") ?? [];
        $shoppingLists = array_merge($createdLists, $sharedLists);
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
        $data = [
            'name' => $request->name,
            'user_id' => $userId,
            'created_at' => now()->toIso8601String(),
        ];

        // Guardar a Firebase
        $this->firebase->set("shopping_lists/created/$userId/$listId", $data);

        return redirect()->route('shopping_lists.index');
    }

    // Mostrar una llista de la compra específica
    public function show($listId)
    {
        $userId = auth()->id();
        $shoppingList = $this->firebase->get("shopping_lists/created/$userId/$listId") ??
                       $this->firebase->get("shopping_lists/shared/$userId/$listId");

        if (!$shoppingList) {
            abort(404, 'Llista no trobada');
        }

        $categories = $this->firebase->get("categories/$listId") ?? [];
        $items = [];
        foreach ($categories as $categoryId => $category) {
            $items[$categoryId] = $this->firebase->get("items/$listId/$categoryId") ?? [];
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

        return redirect()->route('shopping_lists.index');
    }

    // Esborrar una llista de la compra
    public function destroy($listId)
    {
        $userId = auth()->id();
        $path = $this->firebase->get("shopping_lists/created/$userId/$listId") 
                ? "shopping_lists/created/$userId/$listId"
                : "shopping_lists/shared/$userId/$listId";

        $this->firebase->delete($path);
        return redirect()->route('shopping_lists.index');
    }

    // Compartir una llista amb un altre usuari
    public function share(Request $request, $listId)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Usuari no trobat']);
        }

        $userId = auth()->id();
        $list = $this->firebase->get("shopping_lists/created/$userId/$listId");
        if (!$list) {
            abort(404, 'Llista no trobada');
        }

        $this->firebase->set("shopping_lists/shared/{$user->id}/$listId", $list);
        return redirect()->route('shopping_lists.index')->with('success', 'Llista compartida correctament');
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

        // Taula de classificació automàtica
        $productCategories = [
            'llet' => 'Làctics',
            'formatge' => 'Làctics',
            'iogurt' => 'Làctics',
            'pa' => 'Forn',
            'croissant' => 'Forn',
            'poma' => 'Fruites',
            'plàtan' => 'Fruites',
            'patates' => 'Verdures',
            'ceba' => 'Verdures',
        ];

        $itemName = strtolower($request->name);
        $categoryName = $productCategories[$itemName] ?? 'Altres';

        // Buscar o crear categoria
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

        // Crear ítem
        $itemId = uniqid();
        $this->firebase->set("items/$listId/$categoryId/$itemId", [
            'name' => $request->name,
            'is_completed' => false,
            'tag' => $request->tag ?? '',
            'created_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('shopping_lists.show', $listId)->with('success', 'Ítem afegit correctament');
    }

    // Actualitzar un ítem (marcar com completat)
    public function updateItem(Request $request, $listId, $itemId)
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
        $isCompleted = $request->has('is_completed') ? true : false;

        $this->firebase->update("items/$listId/$categoryId/$itemId", [
            'is_completed' => $isCompleted,
            'updated_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('shopping_lists.show', $listId)->with('success', 'Ítem actualitzat correctament');
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
}