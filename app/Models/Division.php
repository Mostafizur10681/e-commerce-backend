<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'bn_name',
        'code',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
