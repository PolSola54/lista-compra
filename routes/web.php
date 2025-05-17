<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Services\FirebaseService;

Route::get('/test-firebase', function (FirebaseService $firebase) {
    $firebase->set('prova/test', ['missatge' => 'Hola Firebase des de Laravel 12']);
    return 'OK!';
});

use App\Http\Controllers\ShoppingListController;

Route::resource('shopping-lists', ShoppingListController::class);

