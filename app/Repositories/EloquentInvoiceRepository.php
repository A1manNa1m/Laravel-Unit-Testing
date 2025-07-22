<?php

namespace App\Repositories;

use App\Models\Invoice;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findOrFail(int $id): Invoice
    {
        return Invoice::findOrFail($id);
    }

    public function sumPayments(int $invoiceId): int
    {
        $invoice = Invoice::findOrFail($invoiceId);
        return $invoice->payments()->sum('amount');
    }

    public function save(Invoice $invoice): bool
    {
        return $invoice->save();
    }
}
