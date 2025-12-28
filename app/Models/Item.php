<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'unit',
        'balance',
    ];

    protected function items(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }
}
