<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'banner_image', // <--- This allows the banner to be saved
        'parent_id',
        'status',
        'sort_order'
    ];

    // Relationship to get children (Subcategories)
    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Relationship to get parent
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}