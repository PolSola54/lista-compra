<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Services\FirebaseService;
use App\Http\Controllers\ShoppingListController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-firebase', function (FirebaseService $firebase) {
    $firebase->set('prova/test', ['missatge' => 'Hola Firebase des de Laravel 12']);
    return 'OK!';
});


Route::get('/test-routes', function () {
    return view('test-routes');
})->middleware('auth');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas para las listas de compra
    Route::get('shopping-lists', [ShoppingListController::class, 'index'])->name('shopping_lists.index');
    Route::get('shopping-lists/create', [ShoppingListController::class, 'create'])->name('shopping_lists.create');
    Route::post('shopping-lists', [ShoppingListController::class, 'store'])->name('shopping_lists.store');
    Route::get('shopping-lists/{listId}', [ShoppingListController::class, 'show'])->name('shopping_lists.show');
    Route::get('shopping-lists/{listId}/edit', [ShoppingListController::class, 'edit'])->name('shopping_lists.edit');
    Route::patch('shopping-lists/{listId}', [ShoppingListController::class, 'update'])->name('shopping_lists.update');
    Route::delete('shopping-lists/{listId}', [ShoppingListController::class, 'destroy'])->name('shopping_lists.destroy');
    Route::post('shopping-lists/{listId}/share', [ShoppingListController::class, 'share'])->name('shopping_lists.share');
    Route::post('shopping-lists/{listId}/items', [ShoppingListController::class, 'storeItem'])->name('shopping_lists.items.store');
    Route::patch('shopping-lists/{listId}/items/{itemId}', [ShoppingListController::class, 'updateItem'])->name('shopping_lists.items.update');
    Route::delete('shopping-lists/{listId}/items/{itemId}', [ShoppingListController::class, 'destroyItem'])->name('shopping_lists.items.destroy');
    Route::delete('shopping-lists/{listId}/categories/{categoryId}', [ShoppingListController::class, 'destroyCategory'])->name('shopping_lists.categories.destroy');
});

require __DIR__.'/auth.php';