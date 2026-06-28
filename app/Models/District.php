<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'division_id',
        'name',
        'bn_name',
        'code',
        'status',
    ];

    protected $casts = [
        'division_id' => 'integer',
        'status' => 'integer',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function thanas(): HasMany
    {
        return $this->hasMany(Thana::class);
    }
}
