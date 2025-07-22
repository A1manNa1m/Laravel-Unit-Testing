<?php

namespace App\Repositories;

use App\Models\Invoice;

interface InvoiceRepositoryInterface
{
    public function findOrFail(int $id): Invoice;

    public function sumPayments(int $invoiceId): int;

    public function save(Invoice $invoice): bool;
}
