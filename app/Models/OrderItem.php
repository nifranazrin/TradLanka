<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// Models are usually in the same namespace, but explicit imports are fine
use App\Models\Product;
use App\Models\Order;
use App\Models\ProductVariant; 

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Explicit table name
     */
    protected $table = 'order_items';

    /**
     * Mass assignable fields
     * Ensure these match your database migration columns exactly.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id', // Changed to match the Controller we just updated
        'qty',
        'price',
    ];

    /**
     * --------------------------------
     * Relationships
     * --------------------------------
     */

    /**
     * Each order item belongs to ONE product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Each order item belongs to ONE order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Relationship to get the specific Size/Variant
     */
    public function variant(): BelongsTo
    {
        // 'variant_id' is the foreign key on this table
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}