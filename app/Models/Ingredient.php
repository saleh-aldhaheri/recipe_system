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
        parent::boot();

        static::creating(function (Ingredient $ingredient) {
            $ingredient->load('item');
            $ingredientService = new ingredientsService;
            $ingredientService->checkItem($ingredient);
            $ingredientService->updateItemOnCreate($ingredient);
        });

        static::updating(function (Ingredient $ingredient) {
            $ingredient->load('item');
            $ingredientService = new ingredientsService;
            $ingredientService->checkItem($ingredient);
            $ingredientService->updateItemOnUpdate($ingredient);
        });

        static::deleting(function (Ingredient $ingredient) {
            $ingredient->load('item');
            (new ingredientsService)->updateItemOnDelete($ingredient);
        });

    }
}
