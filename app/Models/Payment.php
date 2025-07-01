<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'amount',
        'paid_method',
        'paid_at'
    ];

    public function customers() {
        return $this -> belongsTo(Customer::class);
    }

    public function invoices()
    {
        return $this->belongsTo(Invoice::class);
    }
}
