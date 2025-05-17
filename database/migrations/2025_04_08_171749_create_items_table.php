<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de l'element
            $table->boolean('is_completed')->default(false); // Si l'element està completat
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Relació amb la categoria
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
}
