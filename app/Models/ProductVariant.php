<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $table = 'product_variants';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'product_id',
        'unit_label',   // e.g. 100g, 250g, 1kg, 50ml
        'price',        // price for this variant
        'stock',        // ✅ Added stock here so it saves to the database
    ];

    /**
     * Relationship: Variant belongs to a Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}