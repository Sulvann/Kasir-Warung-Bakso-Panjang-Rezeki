<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'category_id',
        'name',
        'image',
        'price',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function productIngredients()
    {
        return $this->hasMany(ProductIngredient::class, 'product_id', 'product_id');
    }

    public function ingredients()
    {
        return $this->belongsToMany(
                Ingredient::class,
                'product_ingredients',
                'product_id',
                'ingredient_id',
                'product_id',
                'ingredient_id'
            )
            ->withPivot('quantity', 'product_ingredient_id', 'deleted_at')
            ->whereNull('product_ingredients.deleted_at')
            ->withTimestamps();
    }
}
