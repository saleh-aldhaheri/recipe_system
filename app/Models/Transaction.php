<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /**
     * Indicates if the model should be timestamped.
     * Transactions are historical records and don't need updating.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'recipe_id',
        'qty_used',
        'balance_after',
        'balance_before',
        'type',
        'operation',
        'created_at',
    ];

    protected $casts = [
        'type' => 'string',
        'qty_used' => 'decimal:3',
        'balance_before' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'created_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Transaction $transaction) {
            $transaction->created_at = Carbon::now();
        });
    }
}
