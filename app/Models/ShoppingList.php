<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ShoppingList;


class ShoppingList extends Model
{
    use HasFactory;

    // Definir la taula associada (si és diferent del plural del nom del model)
    protected $table = 'shopping_lists';

    // Quins camps poden ser assignats en massiva
    protected $fillable = [
        'name',    // Nom de la llista
        'user_id', // Id de l'usuari que crea la llista
    ];

    // Relacions amb altres models (si n'hi ha)
    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}
