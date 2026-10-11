<?php

namespace App\Services;

use App\Models\CustomerPayment;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfInvoiceService
{
    /**
     * Render PDF Faktur Invoice Pelatihan
     */
    public function generateInvoicePdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'company', 'items', 'batch.program', 'paymentAllocations.customerPayment']);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Invoice_{$invoice->invoice_number}.pdf");
    }

    /**
     * Render PDF Kwitansi / Bukti Pembayaran Resmi
     */
    public function generateReceiptPdf(CustomerPayment $payment)
    {
        $payment->load(['customer', 'company', 'cashBankAccount', 'allocations.invoice']);

        $pdf = Pdf::loadView('payments.receipt_pdf', compact('payment'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Kwitansi_{$payment->payment_number}.pdf");
    }
}
