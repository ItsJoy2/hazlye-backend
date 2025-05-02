<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_colors')
            ->withTimestamps();
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function setCodeAttribute($value)
{
    // Ensure hex codes start with #
    $value = ltrim($value, '#');
    $this->attributes['code'] = '#'.$value;
}
}