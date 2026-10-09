<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'target_participants' => 'decimal:2',
        'target_revenue_per_pax' => 'decimal:2',
        'total_revenue_budget' => 'decimal:2',
        'total_cost_budget' => 'decimal:2',
        'projected_gross_margin' => 'decimal:2',
        'projected_margin_percentage' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TrainingBatch::class, 'batch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(BudgetApproval::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
