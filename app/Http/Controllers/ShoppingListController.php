<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Kreait\Firebase\Database;


class ShoppingListController extends Controller
{
    // Mostrar totes les llistes de la compra de l'usuari
    public function index()
    {
        $shoppingLists = $this->database->getReference('shopping_lists')->getValue();
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

        ShoppingList::create([
            'name' => $request->name,
            'user_id' => auth()->id(), // Usuari que crea la llista
        ]);

        return redirect()->route('shopping_lists.index');
    }

    // Mostrar una llista de la compra específica
    public function show(ShoppingList $shoppingList)
    {
        $categories = $shoppingList->categories;
        return view('shopping_lists.show', compact('shoppingList', 'categories'));
    }

    // Editar una llista de la compra
    public function edit(ShoppingList $shoppingList)
    {
        return view('shopping_lists.edit', compact('shoppingList'));
    }

    // Actualitzar una llista de la compra
    public function update(Request $request, ShoppingList $shoppingList)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $shoppingList->update([
            'name' => $request->name,
        ]);

        return redirect()->route('shopping_lists.index');
    }

    // Esborrar una llista de la compra
    public function destroy(ShoppingList $shoppingList)
    {
        $shoppingList->delete();
        return redirect()->route('shopping_lists.index');
    }
}
