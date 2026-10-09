<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'default_daily_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function trainingBatches(): HasMany
    {
        return $this->hasMany(TrainingBatch::class);
    }

    public function vendorBills(): HasMany
    {
        return $this->hasMany(VendorBill::class);
    }
}
