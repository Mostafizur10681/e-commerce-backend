<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Thana extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'district_id',
        'name',
        'bn_name',
        'code',
        'postal_code',
        'status',
    ];

    protected $casts = [
        'district_id' => 'integer',
        'status' => 'integer',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function division(): HasOneThrough
    {
        return $this->hasOneThrough(
            Division::class,
            District::class,
            'id', // Foreign key on intermediate table (districts.id)
            'id', // Foreign key on target table (divisions.id)
            'district_id', // Local key on this table (thanas.district_id)
            'division_id' // Local key on intermediate table (districts.division_id)
        );
    }
}
