<?php

namespace App\Models;

use App\Services\ingredientsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingredient extends Model
{
    protected $fillable = [
        'recipe_id',
        'item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    protected static function boot()
    {
        static::created(function (Ingredient $ingredient) {
            $ingredientService = new ingredientsService;
            $ingredientService->checkItem($ingredient);
            $ingredientService->updateItemOnCreate($ingredient);
        });

        static::updated(function (Ingredient $ingredient) {
            $ingredientService = new ingredientsService;
            $ingredientService->checkItem($ingredient);
            $ingredientService->updateItemOnUpdate($ingredient);
        });

        static::deleted(function (Ingredient $ingredient) {
            (new ingredientsService)->updateItemOnDelete($ingredient);
        });

        static::saved(function (Ingredient $ingredient) {
            $ingredientService = new ingredientsService;
            $ingredientService->checkItem($ingredient);
            $ingredientService->updateItemOnCreate($ingredient);
        });
    }
}
