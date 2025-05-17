<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ShoppingList;

class Item extends Model
{
    use HasFactory;

    // Taula associada
    protected $table = 'items';

    // Quins camps poden ser assignats en massiva
    protected $fillable = ['name', 'category_id', 'is_completed'];

    // Relació amb la categoria
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
