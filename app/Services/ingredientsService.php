<?php

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Models\Ingredient;
use App\Models\Item;
use Illuminate\Database\Capsule\Manager as DB;

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
            $newItem->balance -= $diff;
            $newItem->save();

            return;
        }

        $oldItem->balance += $oldQuantity;
        $oldItem->save();

        $newItem->balance -= $newQuantity;
        $newItem->save();
    }

    public function storeIngredients(array $ingredients)
    {
        $errors = DB::connection()->transaction(function () use ($ingredients) {
            foreach ($ingredients as $ingredient) {
                try {
                    Ingredient::create($ingredient);
                } catch (ValidationException $e) {
                    $error[] = [
                        'message' => $e,
                        'ingredient' => $ingredient,
                    ];
                }
            }
        });

        return $errors;
    }
    
    public function deleteIngredients(array  $ingredients)  
    { 
        try{  
          DB::connection()->transaction(function() use($ingredients) {  
              foreach($ingredients as $ingredient) { 
                 Ingredient::deleted($ingredient); 
              }
          });  
        }catch(ValidationException $e) { 
         
        }

    }
    // public function updateIngredients(array $ingredients) {
    //      $errors = DB::connection()->transaction(function() use ($ingredients) {
    //         foreach($ingredients as $ingredient) {
    //             try {
    //                 $Oldingredient
    //                 Ingredient::updated($ingredient);
    //             }catch (Throwable $e) {
    //                 $error[] = [
    //                     'message' =>  $e,
    //                     'ingredient' => $ingredient
    //                 ];
    //             }
    //         }
    //    });

    //    return $errors;
    // }
}
