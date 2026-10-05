<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'description', 'quantity', 'price', 'is_published', 'in_stock', 'brand_id', 'category_id'])]
class Product extends Model
{

    use SoftDeletes;
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_published' => 'boolean',
            'in_stock' => 'boolean',
        ];
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }


     protected static function booted()
    {
        static::creating(function (Product $product) {
            $product->slug = Str::slug($product->title);
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('title')) {
                $product->slug = Str::slug($product->title);
            }
        });
    }
}
