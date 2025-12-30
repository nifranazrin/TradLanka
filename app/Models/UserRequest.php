<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Since we renamed 'seller_requests' to 'user_requests'.
     */
    protected $table = 'user_requests';

    /**
     * The attributes that are mass assignable.
     * Added 'role' to store 'seller' or 'delivery'.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'nic_number',
        'preferred_name',
        'address',
        'nic_image',
        'status',
        'role', // Added to distinguish position applied for
    ];
}