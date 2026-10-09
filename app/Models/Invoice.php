<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_ppn_rate' => 'decimal:2',
        'tax_ppn_amount' => 'decimal:2',
        'tax_pph23_rate' => 'decimal:2',
        'tax_pph23_amount' => 'decimal:2',
        'down_payment_applied' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'program_id');
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
        return $this->hasMany(InvoiceItem::class);
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
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
        return $query->whereIn('status', ['issued', 'partial', 'overdue']);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', ['issued', 'partial'])
            ->where('due_date', '<', now()->toDateString());
    }
}
