<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'customer_id' => $this->customer_id,
            'invoice_id' => $this->invoice_id,
            'amount' => $this->amount,
            'paid_method' => $this->paid_method,
            'paid_at' => $this->paid_at,
        ];
    }
}