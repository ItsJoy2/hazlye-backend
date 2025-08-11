<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReviewImage extends Model
{
    use HasFactory;

    protected $fillable = ['review_id', 'image_path'];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the full URL for the image
     */
    public function getImageUrlAttribute()
    {
        return Storage::url($this->image_path);
    }

    /**
     * Delete the image file when the model is deleted
     */
    protected static function booted()
    {
        static::deleted(function ($reviewImage) {
            Storage::delete($reviewImage->image_path);
        });
    }

    /**
     * Scope to get only images of a specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}