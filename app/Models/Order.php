<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'name',
        'phone',
        'address',
        'district',
        'thana',
        'subtotal',
        'delivery_charge',
        'discount',
        'total',
        'coupon_code',
        'status',
        'comment',
        'delivery_option_id', 
    ];


    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }
    public function returnStock()
{
    foreach ($this->items as $item) {
        $product = $item->product;

        if ($product->has_variants && $item->variant_option_id) {
            $variantOption = ProductVariantOption::find($item->variant_option_id);
            if ($variantOption) {
                $variantOption->increment('stock', $item->quantity);
                $product->updateStock();
            }
        } else {
            $product->increment('total_stock', $item->quantity);
        }
    }
}

public function deliveryOption()
{
    return $this->belongsTo(DeliveryOption::class, 'delivery_option_id');
}

}