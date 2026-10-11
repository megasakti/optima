<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetApproval;
use App\Models\TrainingBatch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BudgetService
{
    /**
     * Buat atau perbarui Rencana Anggaran Biaya (RAB) untuk batch pelatihan.
     *
     * @param array{
     *     company_id: int,
     *     batch_id: int,
     *     target_participants: float,
     *     target_revenue_per_pax: float,
     *     notes?: string|null,
     *     items: array<array{
     *         account_id?: int|null,
     *         category: string,
     *         description: string,
     *         quantity: float,
     *         unit: string,
     *         unit_price: float
     *     }>
     * } $data
     */
    public function createOrUpdateBudget(array $data): Budget
    {
        return DB::transaction(function () use ($data) {
            $companyId = $data['company_id'];
            $batchId = $data['batch_id'];
            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw new InvalidArgumentException('RAB wajib memiliki minimal 1 rincian pos anggaran biaya.');
            }

            $batch = TrainingBatch::where('company_id', $companyId)->findOrFail($batchId);

            $targetPax = (float) $data['target_participants'];
            $pricePerPax = (float) $data['target_revenue_per_pax'];
            $totalRevenueBudget = $targetPax * $pricePerPax;

            // 1. Hitung Total Biaya Anggaran
            $totalCostBudget = 0.0;
            $processedItems = [];

            $categoryMap = [
                'certification' => 'lsp_certification',
                'trainer' => 'trainer_fee',
                'venue' => 'hotel_venue',
                'catering' => 'catering',
                'materials' => 'training_kit_materials',
                'operational' => 'contingency_other',
                'transport' => 'transport_travel',
                'marketing' => 'marketing',
                'lsp_certification' => 'lsp_certification',
                'trainer_fee' => 'trainer_fee',
                'hotel_venue' => 'hotel_venue',
                'training_kit_materials' => 'training_kit_materials',
                'transport_travel' => 'transport_travel',
                'contingency_other' => 'contingency_other',
            ];

            foreach ($items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $lineTotal = $qty * $price;
                $totalCostBudget += $lineTotal;

                $rawCat = $item['category'] ?? 'training_kit_materials';
                $normalizedCat = $categoryMap[$rawCat] ?? 'training_kit_materials';

                $processedItems[] = [
                    'account_id' => $item['account_id'] ?? null,
                    'category' => $normalizedCat,
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? 'pax',
                    'unit_price' => $price,
                    'total_budget' => $lineTotal,
                ];
            }

            // 2. Hitung Margin Laba Kotor
            $grossMargin = $totalRevenueBudget - $totalCostBudget;
            $marginPct = ($totalRevenueBudget > 0) ? ($grossMargin / $totalRevenueBudget) * 100 : 0.0;

            // 3. Generate Nomor RAB unik
            $user = Auth::user();
            $existingBudget = Budget::where('company_id', $companyId)->where('batch_id', $batchId)->first();

            if ($existingBudget) {
                $budget = $existingBudget;
                $budget->update([
                    'target_participants' => $targetPax,
                    'target_revenue_per_pax' => $pricePerPax,
                    'total_revenue_budget' => $totalRevenueBudget,
                    'total_cost_budget' => $totalCostBudget,
                    'projected_gross_margin' => $grossMargin,
                    'projected_margin_percentage' => $marginPct,
                    'notes' => $data['notes'] ?? null,
                ]);

                // Hapus item lama dan buat baru
                $budget->items()->delete();
            } else {
                $budgetNumber = $this->generateBudgetNumber($companyId);
                $budget = Budget::create([
                    'company_id' => $companyId,
                    'batch_id' => $batchId,
                    'budget_number' => $budgetNumber,
                    'target_participants' => $targetPax,
                    'target_revenue_per_pax' => $pricePerPax,
                    'total_revenue_budget' => $totalRevenueBudget,
                    'total_cost_budget' => $totalCostBudget,
                    'projected_gross_margin' => $grossMargin,
                    'projected_margin_percentage' => $marginPct,
                    'status' => 'draft',
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $user?->id ?? 1,
                ]);
            }

            // 4. Buat Budget Items
            foreach ($processedItems as $itemData) {
                $budget->items()->create($itemData);
            }

            // 5. Update Target Peserta & Harga di Batch jika berubah
            $batch->update([
                'target_participants' => (int) $targetPax,
                'price_per_participant' => $pricePerPax,
            ]);

            // 6. Audit Trail
            AuditTrailService::log(
                'save_budget',
                $budget,
                null,
                ['budget_number' => $budget->budget_number, 'margin_pct' => $marginPct],
                "Penyusunan RAB {$budget->budget_number} Batch {$batch->batch_code} (Target Margin: ".number_format($marginPct, 1).'%)'
            );

            return $budget;
        });
    }

    /**
     * Ajukan RAB ke Finance Manager untuk persetujuan.
     */
    public function submitBudget(Budget $budget, ?string $comment = null): Budget
    {
        if (! in_array($budget->status, ['draft', 'revised'])) {
            throw new InvalidArgumentException("RAB dengan status {$budget->status} tidak dapat diajukan kembali.");
        }

        $user = Auth::user();

        $budget->update(['status' => 'submitted']);

        BudgetApproval::create([
            'budget_id' => $budget->id,
            'user_id' => $user?->id ?? 1,
            'action' => 'submitted',
            'comment' => $comment ?: 'RAB diajukan untuk ditinjau oleh Finance Manager',
        ]);

        AuditTrailService::log(
            'submit_budget',
            $budget,
            ['status' => 'draft'],
            ['status' => 'submitted'],
            "Pengajuan persetujuan RAB {$budget->budget_number}"
        );

        return $budget;
    }

    /**
     * Setujui RAB oleh Finance Manager.
     */
    public function approveBudget(Budget $budget, User $approver, ?string $comment = null): Budget
    {
        if ($budget->status !== 'submitted') {
            throw new InvalidArgumentException("Hanya RAB berstatus 'submitted' yang dapat disetujui.");
        }

        $budget->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        BudgetApproval::create([
            'budget_id' => $budget->id,
            'user_id' => $approver->id,
            'action' => 'approved',
            'comment' => $comment ?: 'RAB disetujui oleh Finance Manager',
        ]);

        AuditTrailService::log(
            'approve_budget',
            $budget,
            ['status' => 'submitted'],
            ['status' => 'approved'],
            "Persetujuan RAB {$budget->budget_number} oleh {$approver->name}"
        );

        return $budget;
    }

    /**
     * Generate Nomor RAB format RAB-YYYY-MM-XXXX
     */
    protected function generateBudgetNumber(int $companyId): string
    {
        $yearMonth = date('Y-m');
        $prefix = 'RAB-'.$yearMonth.'-';

        $lastNumber = Budget::where('company_id', $companyId)
            ->where('budget_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('budget_number');

        if ($lastNumber) {
            $lastSeq = (int) substr($lastNumber, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix.str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
