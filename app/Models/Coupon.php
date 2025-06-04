<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'apply_to_all', // New field
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'apply_to_all' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function isValidForProduct($subtotal, $productId = null): bool
    {
        $now = now();

        $valid = $this->is_active &&
                $now->between($this->start_date, $this->end_date) &&
                $subtotal >= $this->min_purchase;

        if (!$valid) return false;

        // If apply_to_all is true, the coupon is valid for all products
        if ($this->apply_to_all) return true;

        // Otherwise check if the product is associated with the coupon
        if ($productId) {
            return $this->products()->where('product_id', $productId)->exists();
        }

        return false;
    }

    public function calculateDiscount($subtotal, $productId = null)
    {
        if (!$this->isValidForProduct($subtotal, $productId)) {
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