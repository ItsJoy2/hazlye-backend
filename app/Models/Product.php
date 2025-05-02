<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'productId',
        'name',
        'slug',
        'description',
        'regular_price',
        'category_id',
        'main_image',
        'featured',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
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

    public function getMainImageAttribute()
    {
        return $this->variants->first()->image ?? null;
    }
}