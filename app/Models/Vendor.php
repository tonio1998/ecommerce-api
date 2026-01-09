<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_name',
        'description',
        'logo',
        'address',
        'latitude',
        'longitude',
        'is_active'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function staff()
    {
        return $this->hasMany(VendorUser::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(VendorOrder::class);
    }

    public function payouts()
    {
        return $this->hasMany(VendorPayout::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }
}
