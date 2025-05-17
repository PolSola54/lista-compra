<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ShoppingList;

class Category extends Model
{
    use HasFactory;

    // Taula associada
    protected $table = 'categories';

    // Quins camps poden ser assignats en massiva
    protected $fillable = ['name', 'shopping_list_id'];

    // Relació amb el model ShoppingList
    public function shoppingList()
    {
        return $this->belongsTo(ShoppingList::class);
    }

    // Relació amb els elements de la categoria
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
