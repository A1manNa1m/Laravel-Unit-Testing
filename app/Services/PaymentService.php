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
    
    public function update(int $paymentId, array $data): Payment
    {
        // 1. Get the existing payment (through repo)
        $payment = $this->payments->findOrFail($paymentId);

        // 2. Determine the target invoice (might change if invoice_id provided)
        $invoiceId = $data['invoice_id'] ?? $payment->invoice_id;
        $invoice   = $this->invoices->findOrFail($invoiceId);

        // 3. Determine new amount (or fallback to old)
        $newAmount = $data['amount'] ?? $payment->amount;

        // 4. Adjust totals: remove old payment, then add new
        $totalPaid = $this->invoices->sumPayments($invoiceId) - $payment->amount;
        $newTotal  = $totalPaid + $newAmount;

        // 5. Update invoice status
        if ($newTotal == $invoice->amount) {
            $invoice->status    = 'FP';
            $invoice->paid_date = now();
        } elseif ($newTotal > $invoice->amount) {
            $invoice->status    = 'OP';
            $invoice->paid_date = now();
        } elseif ($newTotal > 0) {
            $invoice->status    = 'HP';
            $invoice->paid_date = null;
        } else {
            $invoice->status    = 'B';
            $invoice->paid_date = null;
        }

        $this->invoices->save($invoice);

        // 6. Update the payment itself
        $this->payments->update($payment, $data);

        return $payment;
    }

    public function destroy(int $paymentId): bool
    {
        // 1. Find the payment to delete
        $payment = $this->payments->findOrFail($paymentId);
        $invoice = $this->invoices->findOrFail($payment->invoice_id);

        // 2. Calculate new total after removing this payment
        $totalPaid = $this->invoices->sumPayments($invoice->id);
        $newTotal  = $totalPaid - $payment->amount;

        // 3. Update invoice status
        if ($newTotal == $invoice->amount) {
            $invoice->status    = 'FP';
            $invoice->paid_date = now();
        } elseif ($newTotal > $invoice->amount) {
            $invoice->status    = 'OP';
            $invoice->paid_date = now();
        } elseif ($newTotal > 0) {
            $invoice->status    = 'HP';
            $invoice->paid_date = null;
        } else {
            $invoice->status    = 'B';
            $invoice->paid_date = null;
        }

        $this->invoices->save($invoice);

        // 4. Delete the payment
        return $this->payments->delete($payment);
    }


}
