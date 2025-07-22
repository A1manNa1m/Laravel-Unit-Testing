<?php

namespace App\Repositories;

use App\Models\Payment;

interface PaymentRepositoryInterface
{
    public function create(array $data): Payment;
}
