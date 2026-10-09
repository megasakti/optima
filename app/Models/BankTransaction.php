<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BankTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'is_reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    public function cashBankAccount(): BelongsTo
    {
        return $this->belongsTo(CashBankAccount::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
