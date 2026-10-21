<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['category_id', 'name', 'description', 'price', 'stock', 'image_path', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'stock' => 'integer', 'revision' => 'integer', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return Storage::disk('public')->url($this->image_path);
        }

        $categoryName = strtolower($this->category->name ?? '');
        $productName = strtolower($this->name ?? '');

        if (str_contains($categoryName, 'kebab') || str_contains($productName, 'kebab')) {
            return asset('images/kebab-placeholder.svg');
        }

        return asset('images/menu-placeholder.svg');
    }
}
