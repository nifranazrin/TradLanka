<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * ---------------------------------
     * Mass assignes
     * ---------------------------------
     */
    protected $fillable = [
        'user_id',

        // Customer details
        'fname',
        'lname',
        'email',
        'phone',

        // Address
        'address1',
        'address2',
        'city',
        'state',
        'zipcode',
        'message',

        // Order info
        'total_price',
        'tracking_no',
        'payment_mode',
        'payment_id',

        // Order status
        'status',
        'delivery_boy_id',
        'cancel_reason',
        'currency', 
    ];

    /**
     * ---------------------------------
     * Type casting
     * ---------------------------------
     */
    protected $casts = [
        'total_price' => 'decimal:2',
        'status'      => 'integer',
    ];

    /**
     * ---------------------------------
     * Relationships
     * ---------------------------------
     */

    // Order belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Existing relationship (DO NOT REMOVE)
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * ✅ ALIAS FOR SELLER CONTROLLER
     * This fixes: Call to undefined relationship [items]
     * NO logic change, NO refactor
     */
    public function items(): HasMany
    {
        return $this->orderItems();
    }

    public function deliveryBoy()
{
    return $this->belongsTo(Staff::class, 'delivery_boy_id');
}
}
