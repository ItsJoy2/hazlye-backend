<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'charge',
        'is_active',
    ];

    protected $casts = [
        'charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}