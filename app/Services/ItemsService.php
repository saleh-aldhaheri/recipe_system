<?php

namespace App\Services;

use App\Enums\TransactionTypeEnum;
use App\Models\Item;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Throwable;

class ItemsService
{
    public function updateItem(Item $item, array $data): void
    {
        try {
            DB::connection()->transaction(function () use ($item, $data) {
                $oldBalance = $item->balance;

                $item->update($data);
                $item->refresh();

                if (isset($data['balance']) && $oldBalance != $item->balance) {
                    $diff = abs($item->balance - $oldBalance);
                    $operation = $item->balance > $oldBalance ? '+' : '-';
                    (new TransactionsService)->createTransaction(
                        $item,
                        TransactionTypeEnum::UPDATE_ITEM,
                        $diff,
                        $operation,
                        $oldBalance
                    );
                }

                $item->refresh();
            });
        } catch (Throwable $e) {

            throw new Exception('Unable to update the item');
        }
    }
}
