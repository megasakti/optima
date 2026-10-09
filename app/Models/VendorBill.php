<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorBill extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'approved_at' => 'datetime',
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

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TrainingBatch::class, 'batch_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorBillItem::class);
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(VendorPaymentAllocation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', ['approved', 'partial']);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', ['approved', 'partial'])
            ->where('due_date', '<', now()->toDateString());
    }
}
