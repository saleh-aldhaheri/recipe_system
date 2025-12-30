<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case UPDATE_ITEM = 'update item';
    case RECIPE_USAGE = 'recipe usage';
}
