<?php

namespace App\Services;

use App\Models\DeletionRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeletionService
{
    /**
     * Memproses penghapusan atau mengajukan permintaan penghapusan (approval Super Admin)
     */
    public function requestOrDelete(User $user, Model $model, string $label, ?string $reason = null): array
    {
        $companyId = $user->company_id ?? ($model->company_id ?? 1);
        $reason = $reason ?: 'Permintaan penghapusan data ' . $label;

        // Jika user adalah Super Admin, eksekusi hapus langsung
        if ($user->hasRole('Super Admin')) {
            return DB::transaction(function () use ($user, $model, $label, $reason, $companyId) {
                $snapshot = $model->toArray();
                
                // Cari apakah ada pending deletion request untuk record ini, jika ada approve otomatis
                DeletionRequest::where('model_type', get_class($model))
                    ->where('model_id', $model->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'approved',
                        'approved_by' => $user->id,
                        'actioned_at' => now(),
                    ]);

                $model->delete();

                AuditTrailService::log(
                    'delete',
                    $model,
                    $snapshot,
                    null,
                    "Data {$label} berhasil dihapus permanen oleh Super Admin ({$user->name}). Alasan: {$reason}"
                );

                return [
                    'status' => 'deleted',
                    'message' => "Data \"{$label}\" berhasil dihapus permanen oleh Super Administrator.",
                ];
            });
        }

        // Jika user bukan Super Admin, buat permintaan approval ke Super Admin
        $existing = DeletionRequest::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return [
                'status' => 'already_pending',
                'message' => "Permintaan penghapusan untuk \"{$label}\" sudah pernah diajukan sebelumnya dan sedang menunggu persetujuan Super Administrator.",
            ];
        }

        $deletionRequest = DeletionRequest::create([
            'company_id' => $companyId,
            'requested_by' => $user->id,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'model_label' => $label,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        AuditTrailService::log(
            'delete_request',
            $model,
            null,
            null,
            "Pengajuan hapus untuk \"{$label}\" oleh {$user->name}. Menunggu persetujuan Super Admin. Alasan: {$reason}"
        );

        return [
            'status' => 'pending_approval',
            'message' => "Permintaan penghapusan data \"{$label}\" berhasil diajukan dan sedang menunggu persetujuan (*approval*) Super Administrator.",
            'request_id' => $deletionRequest->id,
        ];
    }

    /**
     * Super Admin menyetujui penghapusan data
     */
    public function approve(DeletionRequest $deletionRequest, User $superAdmin): array
    {
        if (!$superAdmin->hasRole('Super Admin')) {
            throw new \DomainException('Hanya Super Administrator yang berwenang menyetujui penghapusan data.');
        }

        if ($deletionRequest->status !== 'pending') {
            throw new \DomainException("Permintaan penghapusan ini sudah berstatus {$deletionRequest->status}.");
        }

        return DB::transaction(function () use ($deletionRequest, $superAdmin) {
            $target = $deletionRequest->getTargetModel();

            if ($target) {
                $snapshot = $target->toArray();
                $target->delete();

                AuditTrailService::log(
                    'delete',
                    $target,
                    $snapshot,
                    null,
                    "Persetujuan penghapusan disetujui oleh Super Admin ({$superAdmin->name}) untuk data: {$deletionRequest->model_label}"
                );
            }

            $deletionRequest->update([
                'status' => 'approved',
                'approved_by' => $superAdmin->id,
                'actioned_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "Persetujuan berhasil. Data \"{$deletionRequest->model_label}\" telah dihapus dari sistem.",
            ];
        });
    }

    /**
     * Super Admin menolak penghapusan data
     */
    public function reject(DeletionRequest $deletionRequest, User $superAdmin, string $rejectionReason): array
    {
        if (!$superAdmin->hasRole('Super Admin')) {
            throw new \DomainException('Hanya Super Administrator yang berwenang menolak penghapusan data.');
        }

        if ($deletionRequest->status !== 'pending') {
            throw new \DomainException("Permintaan penghapusan ini sudah berstatus {$deletionRequest->status}.");
        }

        $deletionRequest->update([
            'status' => 'rejected',
            'approved_by' => $superAdmin->id,
            'rejection_reason' => $rejectionReason,
            'actioned_at' => now(),
        ]);

        AuditTrailService::log(
            'reject_deletion',
            $deletionRequest,
            null,
            null,
            "Permintaan hapus data \"{$deletionRequest->model_label}\" ditolak oleh Super Admin ({$superAdmin->name}). Alasan: {$rejectionReason}"
        );

        return [
            'success' => true,
            'message' => "Permintaan penghapusan data \"{$deletionRequest->model_label}\" berhasil ditolak.",
        ];
    }
}
