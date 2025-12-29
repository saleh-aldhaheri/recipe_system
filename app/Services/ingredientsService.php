<?php

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Models\Ingredient;
use App\Models\Item;
use App\Models\Recipe;

class ingredientsService
{
    public function checkItem(Ingredient $ingredient)
    {
        $item = $ingredient->item;
        if (! $item) {
            throw new ValidationException([
                'item_id' => "Item with ID {$ingredient->item_id} does not exist",
            ]);
        }

        if ($ingredient->item->balance < $ingredient->quantity) {
            throw new ValidationException(['quantity error' => "ingredient quantity is larger then the exiting balance for the item {$ingredient->item->name}"]);
        }
    }

    public function updateItemOnCreate(Ingredient $ingredient)
    {
        $item = $ingredient->item;
        $item->balance = $item->balance - $ingredient->quantity;
        $item->save();
    }

    public function updateItemOnDelete(Ingredient $ingredient)
    {
        $item = $ingredient->item;
        $item->balance = $item->balance + $ingredient->quantity;
        $item->save();
    }

    public function updateItemOnUpdate(Ingredient $ingredient)
    {
        $oldItemId = $ingredient->getOriginal('item_id');
        $oldQuantity = (float) $ingredient->getOriginal('quantity');

        $newItem = $ingredient->item;
        $newQuantity = (float) $ingredient->quantity;

        $oldItem = $oldItemId === $newItem->id
            ? $newItem
            : Item::find($oldItemId);

        if (! $oldItem || ! $newItem) {
            throw new ValidationException([
                'item_id' => 'One of the related items does not exist',
            ]);
        }

        if ($oldItem->id === $newItem->id) {
            $diff = $newQuantity - $oldQuantity;
            $availableBalance = (float) $newItem->balance + $oldQuantity;

            if ($newQuantity > $availableBalance) {
                throw new ValidationException([
                    'quantity' => "Insufficient balance. Available: {$availableBalance}, Required: {$newQuantity}",
                ]);
            }

            $newItem->balance -= $diff;
            $newItem->save();

            return;
        }

        $oldItem->balance += $oldQuantity;
        $oldItem->save();

        if ($newQuantity > (float) $newItem->balance) {
            throw new ValidationException([
                'quantity' => "Insufficient balance for item '{$newItem->name}'. Available: {$newItem->balance}, Required: {$newQuantity}",
            ]);
        }

        $newItem->balance -= $newQuantity;
        $newItem->save();
    }

    public function storeIngredients(array $ingredients, int $recipeId): array
    {
        $failed = [];

        foreach ($ingredients as $ingredient) {
            try {
                $ingredient['recipe_id'] = $recipeId;
                Ingredient::create($ingredient);
            } catch (\Throwable $e) {
                $failed[] = [
                    'item_id' => $ingredient['item_id'] ?? null,
                    'quantity' => $ingredient['quantity'] ?? null,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return $failed;
    }

    public function updateRecipeIngredients(Recipe $recipe, array $newIngredients): array
    {
        $failed = [];

        $recipe->ingredients()->delete();

        if (! empty($newIngredients)) {
            foreach ($newIngredients as $ingredient) {
                try {
                    $ingredient['recipe_id'] = $recipe->id;
                    Ingredient::create($ingredient);
                } catch (\Throwable $e) {
                    $failed[] = [
                        'item_id' => $ingredient['item_id'] ?? null,
                        'quantity' => $ingredient['quantity'] ?? null,
                        'reason' => $e->getMessage(),
                    ];
                }
            }
        }

        return $failed;
    }
}
