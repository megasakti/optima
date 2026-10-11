<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Participant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'training_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relasi ke data Klien / Perusahaan
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lsp(): BelongsTo
    {
        return $this->belongsTo(Lsp::class);
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function trainingBatch(): BelongsTo
    {
        return $this->belongsTo(TrainingBatch::class);
    }

    /**
     * Nama Perusahaan (ambil dari relasi customer jika ada, fallback ke company_name)
     */
    public function getDisplayCompanyNameAttribute(): string
    {
        if ($this->customer) {
            return $this->customer->name;
        }

        return $this->company_name ?: '-';
    }

    /**
     * Label Kategori Pelatihan
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'public' => 'Public Training',
            'iht' => 'In-House Training (IHT)',
            'webinar' => 'Webinar',
            default => ucfirst((string) $this->category),
        };
    }

    public function getCategoryBadgeAttribute(): string
    {
        return match ($this->category) {
            'public' => 'badge bg-primary-subtle text-primary border border-primary-subtle',
            'iht' => 'badge bg-success-subtle text-success border border-success-subtle',
            'webinar' => 'badge bg-purple-subtle text-purple border border-purple-subtle',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Label Sumber Leads
     */
    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'web' => 'Website',
            'ig' => 'Instagram',
            'bod' => 'BOD / Referral',
            'other' => 'Lainnya',
            default => strtoupper((string) $this->source),
        };
    }

    /**
     * Label Status Pembayaran
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Lunas',
            'partial' => 'Sebagian / DP',
            'unpaid' => 'Belum Bayar',
            default => ucfirst((string) $this->payment_status),
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'badge bg-success-subtle text-success border border-success-subtle',
            'partial' => 'badge bg-warning-subtle text-warning border border-warning-subtle',
            'unpaid' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Label Status Sertifikat
     */
    public function getCertificateStatusLabelAttribute(): string
    {
        return match ($this->certificate_status) {
            'process' => 'Dalam Proses',
            'passed' => 'Kompeten (K)',
            'issued' => 'Sertifikat Terbit',
            'failed' => 'Belum Kompeten (BK)',
            default => ucfirst((string) $this->certificate_status),
        };
    }

    public function getCertificateStatusBadgeAttribute(): string
    {
        return match ($this->certificate_status) {
            'issued' => 'badge bg-success-subtle text-success border border-success-subtle',
            'passed' => 'badge bg-info-subtle text-info border border-info-subtle',
            'process' => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
            'failed' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            default => 'badge bg-secondary',
        };
    }
}
