<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    use HasFactory;

    protected $table = 'contact_settings';

    protected $fillable = [
        'phone',
        'email',
        'address',
        'business_hours_weekday',
        'business_hours_weekend',
        'support_title',
        'support_desc',
        'support_phone',
        'support_image'
    ];
}
