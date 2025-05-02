<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use HasFactory;

    protected $fillable = [
       'activation_amount',
        'bonus_token',
        'min_withdraw',
        'max_withdraw',
        'app_name',
        'logo',
        'favicon',
    ];
}
