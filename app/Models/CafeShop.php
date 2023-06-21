<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CafeShop extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'city',
        'phone_number',
        'time_open',
        'time_close',
        'photoUrl',
        'air_conditioner',
        'total_seats',
        'empty_seats',
        'user_id',
        'star',
        'isOpen'
    ];
    public $timestamps = false;

}
