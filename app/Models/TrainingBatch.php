<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TrainingBatch extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'assessment_date' => 'date',
        'target_participants' => 'integer',
        'actual_participants' => 'integer',
        'price_per_participant' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'program_id');
    }

    public function lsp(): BelongsTo
    {
        return $this->belongsTo(Lsp::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function budget(): HasOne
    {
        return $this->hasOne(Budget::class, 'batch_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'batch_id');
    }

    public function vendorBills(): HasMany
    {
        return $this->hasMany(VendorBill::class, 'batch_id');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'batch_id');
    }
}
