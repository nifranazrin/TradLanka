<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
    'name',
    'email',
    'password',
    'phone',      
    'address',    
    'role',
    'status',
    'image',  
    ];

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class, 'seller_id');
    }
}
