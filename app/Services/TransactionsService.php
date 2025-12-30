<?php

namespace App\Services;

use App\Enums\TransactionTypeEnum;
use App\Exceptions\ValidationException;
use App\Models\Item;
use App\Models\Transaction;

class TransactionsService
{
    public function createTransaction(Item $item, TransactionTypeEnum $transactionType, float $amount, string $operation, float $balanceBefore, ?int $recipeId = null)
    {
        if ($operation != '+' && $operation != '-') {
            throw new ValidationException(['operation' => 'Invalid operation']);
        }

        $balanceAfter = $operation == '+' ? $balanceBefore + $amount : $balanceBefore - $amount;

        Transaction::create([
            'item_id' => $item->id,
            'recipe_id' => $recipeId,
            'qty_used' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'operation' => $operation,
            'type' => $transactionType->value,
        ]);
    }
}
