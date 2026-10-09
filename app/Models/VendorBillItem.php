<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBillItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
