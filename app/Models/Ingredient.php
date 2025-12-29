<?php

namespace App\Models;

use App\Services\ingredientsService;
use App\Models\Item;
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
            if (! $ingredient->item) {
                $item = Item::find($ingredient->item_id);
                if ($item) {
                    $ingredient->setRelation('item', $item);
                }
            }
            $ingredientService = new ingredientsService;
            $ingredientService->checkItem($ingredient);
        });

        static::updating(function (Ingredient $ingredient) {
            $ingredient->load('item');
            if (! $ingredient->item) {
                $item = Item::find($ingredient->item_id);
                if ($item) {
                    $ingredient->setRelation('item', $item);
                }
            }
        });

        static::deleting(function (Ingredient $ingredient) {
            $ingredient->load('item');
            (new ingredientsService)->updateItemOnDelete($ingredient);
        });
        
    }
}
