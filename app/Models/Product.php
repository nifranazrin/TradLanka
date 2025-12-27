<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

// Related models
use App\Models\Category;
use App\Models\Staff;
use App\Models\ProductImage;
use App\Models\OrderItem;

class Product extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'category_id',
        'seller_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'unit_type',
        'image',   // main image
        'status',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'price'      => 'decimal:2',
        'stock'      => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Accessors automatically appended to JSON / array
     */
    protected $appends = [
        'image_url',
        'display_price',
        
    ];

public function getDisplayPriceAttribute()
{
    $originalPrice = $this->price;
    $currency = session('currency', 'LKR');

    if ($currency === 'USD') {
        // Force a calculation even if the API fails for testing
        $rate = cache()->get('usd_rate', 0.0032); // 0.0032 is roughly 1/310
        
        $converted = $originalPrice * $rate;
        return '$' . number_format($converted, 2);
    }

    return 'Rs ' . number_format($originalPrice, 2);
}
    /**
     * -------------------------------------------------
     * Model boot logic (IMPORTANT – unchanged)
     * -------------------------------------------------
     */
    protected static function booted()
    {
        // Generate slug only when creating
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $base = Str::slug($product->name ?: 'product');
                $slug = $base;
                $attempt = 0;

                while (static::where('slug', $slug)->exists() && $attempt < 10) {
                    $slug = $base . '-' . Str::random(6);
                    $attempt++;
                }

                $product->slug = $slug;
            }
        });

        // Prevent slug from changing on update
        static::updating(function ($product) {
            $product->slug = $product->getOriginal('slug');
        });
    }

    /**
     * Use slug for route model binding
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

    /**
     * Product belongs to a category
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Product belongs to a seller (staff)
     * CRITICAL for seller order filtering
     */
    public function seller()
    {
        return $this->belongsTo(Staff::class, 'seller_id');
    }

    /**
     * Product gallery images
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id')
                    ->orderBy('sort_order');
    }

    /**
     * Order items that include this product
     * (SAFE addition – needed for marketplace logic)
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Public URL for main product image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        $clean = preg_replace('/^public\//', '', $this->image);
        $clean = ltrim($clean, '/');

        return Storage::url($clean);
    }

    public function variants()
{
    return $this->hasMany(ProductVariant::class);
}




    /**
     * Public URLs for gallery images
     */
    public function galleryUrls()
    {
        return $this->images
            ->map(function ($img) {
                if (! $img->path) {
                    return null;
                }

                $clean = preg_replace('/^public\//', '', $img->path);
                $clean = ltrim($clean, '/');

                return $clean ? Storage::url($clean) : null;
            })
            ->filter();
        }
 public function reviews()
{
    return $this->hasMany(Review::class)
                ->where('status', 1) // only approved reviews
                ->latest();
}

// app/Models/Product.php

public function averageRating()
{
    // Returns the average of approved reviews
    return $this->reviews()->avg('rating');
}

}
