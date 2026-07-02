<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, Sluggable, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'sale_price',
        'SKU',
        'stock',
        'status',
        'category_id',
        'sub_category',
        'brand',
        'tax',
        'discount',
        'unit',
        'stock_status',
        'featured',
        'best_seller',
        'organic',
        'new_arrival',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
            'status' => 'boolean',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'featured' => 'boolean',
            'best_seller' => 'boolean',
            'organic' => 'boolean',
            'new_arrival' => 'boolean',
            'attributes' => 'array',
        ];
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'price', 'sale_price', 'SKU', 'stock', 'status', 'category_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all images for the product.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
