<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeletionRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'actioned_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Ambil instance target model yang hendak dihapus (jika masih eksis)
     */
    public function getTargetModel(): ?Model
    {
        if (class_exists($this->model_type)) {
            return $this->model_type::find($this->model_id);
        }

        return null;
    }
}
