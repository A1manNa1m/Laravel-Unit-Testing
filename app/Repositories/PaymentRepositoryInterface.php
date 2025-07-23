<?php

namespace App\Repositories;

use App\Models\Payment;

interface PaymentRepositoryInterface
{
    public function create(array $data): Payment;
    
    public function findOrFail(int $id): Payment;

    public function update(Payment $payment, array $data): bool;
}
