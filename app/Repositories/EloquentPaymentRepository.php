<?php

namespace App\Repositories;

use App\Models\Payment;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function findOrFail(int $id): Payment
    {
        return Payment::findOrFail($id);
    }

    public function update(Payment $payment, array $data): bool
    {
        return $payment->update($data);
    }

}
