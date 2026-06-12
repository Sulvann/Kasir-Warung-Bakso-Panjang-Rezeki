<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'ingredient_id';

    protected $fillable = [
        'name',
        'stock',
        'unit',
        'status',
    ];

    public function productIngredients()
    {
        return $this->hasMany(ProductIngredient::class, 'ingredient_id', 'ingredient_id');
    }
}
