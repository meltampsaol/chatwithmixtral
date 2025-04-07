<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'status',
    ];

    /**
     * Define the relationship between an order and a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Example: Additional relationship for items (if you have an items table).
     * Uncomment if needed.
     */
    // public function items()
    // {
    //     return $this->hasMany(Item::class);
    // }
}
