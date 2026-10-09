<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPaymentAllocation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount_applied' => 'decimal:2',
    ];

    public function vendorPayment(): BelongsTo
    {
        return $this->belongsTo(VendorPayment::class);
    }

    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class);
    }
}
