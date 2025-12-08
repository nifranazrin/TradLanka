<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
// It is good practice to import related models, even if in the same namespace
use App\Models\Category;
use App\Models\Staff;
use App\Models\ProductImage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'seller_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',   // main image (front image)
        'status',
    ];

    /**
     * Casts for predictable types.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Append derived attributes when model is serialized.
     */
    protected $appends = [
        'image_url',
    ];

    /**
     * Boot model events to set slug only on create and prevent slug from changing on update.
     */
    protected static function booted()
    {
        // Set slug only when creating (if not provided)
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $base = Str::slug($product->name ?: 'product');

                // ensure uniqueness (try a few times)
                $slug = $base;
                $attempt = 0;
                while (static::where('slug', $slug)->exists() && $attempt < 10) {
                    $slug = $base . '-' . Str::random(6);
                    $attempt++;
                }

                $product->slug = $slug;
            }
        });

        // Prevent accidental slug change on update by restoring original slug
        // This ensures your URLs don't break if you edit a product name later.
        static::updating(function ($product) {
            $product->slug = $product->getOriginal('slug');
        });
    }

    /**
     * Use slug for route model binding (essential for your SEO URLs).
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Relationship: Product belongs to a Category
    // This is the function that allows $product->category->name in your breadcrumbs
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relationship: Product belongs to a Seller (Staff)
    public function seller()
    {
        return $this->belongsTo(Staff::class, 'seller_id');
    }

    // Relationship for additional product images (gallery)
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('sort_order');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    // Helper: full public URL for main image
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) return null;

        // Normalize path: remove leading 'public/' and any leading slashes
        $clean = preg_replace('/^public\//', '', $this->image);
        $clean = ltrim($clean, '/');

        return Storage::url($clean);
    }

    /**
     * Return collection of public URLs for gallery images.
     */
    public function galleryUrls()
    {
        return $this->images->map(function ($img) {
            $p = $img->path ?? null;
            if (! $p) return null;
            $clean = preg_replace('/^public\//', '', $p);
            $clean = ltrim($clean, '/');
            return $clean ? Storage::url($clean) : null;
        })->filter();
    }
}