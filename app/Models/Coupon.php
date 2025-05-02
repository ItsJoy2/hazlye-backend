<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'amount',
        'type',
        'start_date',
        'end_date',
        'min_purchase',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function isValid($subtotal)
    {
        $now = now();

        return $this->is_active &&
               $now->between($this->start_date, $this->end_date) &&
               $subtotal >= $this->min_purchase;
    }

    public function calculateDiscount($subtotal)
    {
        if (!$this->isValid($subtotal)) {
            return 0;
        }

        if ($this->type === 'fixed') {
            return $this->amount;
        }

        if ($this->type === 'percentage') {
            return ($subtotal * $this->amount) / 100;
        }

        return 0;
    }
}