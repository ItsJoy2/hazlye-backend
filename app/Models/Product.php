<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'productId',
        'sku',
        'name',
        'slug',
        'description',
        'regular_price',
        'Purchase_price',
        'category_id',
        'main_image',
        'main_image_2',
        'featured',
    ];

    protected $casts = [
        'regular_price' => 'float',
        'Purchase_price' => 'float',
        'id' => 'integer',
        'category_id' => 'integer',
        'featured' => 'boolean',
    ];
    protected $primaryKey = 'id';
    public $incrementing = true;
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->main_image) {
            return asset('storage/'.$this->main_image);
        }
        return null;
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function approvedReviews()
{
    return $this->reviews()->where('approved', true);
}

}