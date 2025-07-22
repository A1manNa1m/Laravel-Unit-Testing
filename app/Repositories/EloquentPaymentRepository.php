<?php

namespace App\Repositories;

use App\Models\Payment;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }
}
