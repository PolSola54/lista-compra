<?php

use App\Http\Controllers\ShoppingListController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('shopping-lists', [ShoppingListController::class, 'index'])->name('shopping_lists.index');
    Route::get('shopping-lists/create', [ShoppingListController::class, 'create'])->name('shopping_lists.create');
    Route::post('shopping-lists', [ShoppingListController::class, 'store'])->name('shopping_lists.store');
    Route::get('shopping-lists/{listId}', [ShoppingListController::class, 'show'])->name('shopping_lists.show');
    Route::get('shopping-lists/{listId}/edit', [ShoppingListController::class, 'edit'])->name('shopping_lists.edit');
    Route::put('shopping-lists/{listId}', [ShoppingListController::class, 'update'])->name('shopping_lists.update');
    Route::delete('shopping-lists/{listId}', [ShoppingListController::class, 'destroy'])->name('shopping_lists.destroy');

    // Unir-se a una llista
    Route::post('shopping-lists/join', [ShoppingListController::class, 'join'])->name('shopping_lists.join');

    // Gestió d'ítems
    Route::post('shopping-lists/{listId}/items', [ShoppingListController::class, 'storeItem'])->name('shopping_lists.items.store');
    Route::patch('shopping-lists/{listId}/items/{itemId}', [ShoppingListController::class, 'updateItem'])->name('shopping_lists.items.update');
    Route::delete('shopping-lists/{listId}/items/{itemId}', [ShoppingListController::class, 'destroyItem'])->name('shopping_lists.items.destroy');

    // Gestió de categories
    Route::delete('shopping-lists/{listId}/categories/{categoryId}', [ShoppingListController::class, 'destroyCategory'])->name('shopping_lists.categories.destroy');
});

Auth::routes();

Route::get('/', function () {
    return redirect()->route('shopping_lists.index');
});

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');


use App\Services\FirebaseService;

Route::get('/firebase-test', function (FirebaseService $firebase) {
    $firebase->set('prueba', ['estado' => 'ok']);
    return 'Firebase funcionando ✅';
});
