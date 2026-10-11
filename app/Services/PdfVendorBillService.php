<?php

namespace App\Services;

use App\Models\VendorPayment;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfVendorBillService
{
    /**
     * Render PDF Bukti Pengeluaran Kas/Bank (Payment Voucher).
     */
    public function generateVoucherPdf(VendorPayment $payment)
    {
        $payment->load([
            'lsp',
            'vendor',
            'trainer',
            'company',
            'cashBankAccount',
            'allocations.vendorBill.items',
            'createdBy',
            'journalEntry.lines.account',
        ]);

        $pdf = Pdf::loadView('vendor_payments.voucher_pdf', compact('payment'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Payment_Voucher_{$payment->payment_number}.pdf");
    }
}
