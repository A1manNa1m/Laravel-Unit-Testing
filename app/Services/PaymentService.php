<?php
namespace App\Services;

use App\Repositories\InvoiceRepositoryInterface;
use App\Repositories\PaymentRepositoryInterface;
use App\Models\Payment;

class PaymentService
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoices,
        protected PaymentRepositoryInterface $payments
    ) {}

    public function store(int $invoiceId, int $amount): Payment
    {
        $invoice   = $this->invoices->findOrFail($invoiceId);
        $totalPaid = $this->invoices->sumPayments($invoiceId);
        $newTotal  = $totalPaid + $amount;

        // Determine status (unchanged logic)
        if ($newTotal == $invoice->amount) {
            $invoice->status = 'FP';
            $invoice->paid_date = now();
        } elseif ($newTotal > $invoice->amount) {
            $invoice->status = 'OP';
            $invoice->paid_date = now();
        } elseif ($newTotal > 0) {
            $invoice->status = 'HP';
            $invoice->paid_date = null;
        } else {
            $invoice->status = 'B';
            $invoice->paid_date = null;
        }

        $this->invoices->save($invoice);

        return $this->payments->create([
            'invoice_id' => $invoiceId,
            'amount' => $amount,
        ]);
    }
}
