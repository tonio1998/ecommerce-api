<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'amount',
        'status',
        'released_at'
    ];

    protected $casts = [
        'released_at' => 'datetime'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
