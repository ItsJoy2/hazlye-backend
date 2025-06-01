<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'category_id',
        'size_id',
        'total_stock',
        'buy_price',
        'regular_price',
        'discount_price',
        'is_featured',
        'is_offer',
        'is_campaign',
        'main_image'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_offer' => 'boolean',
        'is_campaign' => 'boolean',
        'buy_price' => 'float',
        'size_id' => 'float',
        'regular_price' => 'float',
        'discount_price' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getHasVariantsAttribute()
    {
        return $this->variants()->count() > 0;
    }

    public function updateStock()
    {
        if ($this->has_variants) {
            $this->total_stock = $this->variants->sum(function($variant) {
                return $variant->options->sum('stock');
            });
        }
        $this->save();
    }
    public function size()
{
    return $this->belongsTo(Size::class);
}
public function reviews()
{
    return $this->hasMany(Review::class);
}

}
