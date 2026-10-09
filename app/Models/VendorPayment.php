<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorPayment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lsp(): BelongsTo
    {
        return $this->belongsTo(Lsp::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function cashBankAccount(): BelongsTo
    {
        return $this->belongsTo(CashBankAccount::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(VendorPaymentAllocation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
