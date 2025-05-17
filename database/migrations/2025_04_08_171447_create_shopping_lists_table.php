<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingListsTable extends Migration
{
    /**
     * Executa la migració.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopping_lists', function (Blueprint $table) {
            $table->id(); // ID automàtic
            $table->string('name'); // Nom de la llista
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relació amb l'usuari
            $table->timestamps(); // Dates de creació i actualització
        });
    }

    /**
     * Reverteix la migració.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopping_lists');
    }
}
