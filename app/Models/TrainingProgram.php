<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'duration_days' => 'integer',
        'default_price_per_participant' => 'decimal:2',
        'default_lsp_cost_per_participant' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lsp(): BelongsTo
    {
        return $this->belongsTo(Lsp::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(TrainingBatch::class, 'program_id');
    }
}
