<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount_applied' => 'decimal:2',
    ];

    public function customerPayment(): BelongsTo
    {
        return $this->belongsTo(CustomerPayment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
