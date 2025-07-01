<?php

namespace App\Filter\V1;

use Illuminate\Http\Request;
use App\Filter\ApiFilter;

class PaymentFilter extends ApiFilter {
    protected $safeParams = [
        'customerId' => ['eq'],
        'invoiceId' => ['eq'],
        'amount' => ['eq','lt','lte','gt','gte'],
        'paidMethod' => ['eq'],
        'paidAt' => ['eq','lt','lte','gt','gte']
    ];

    protected $columnMap = [
        'customerId' => 'customer_id',
        'invoiceId' => 'invoice_id',
        'paidMethod' => 'paid_method',
        'paidAt' => 'paid_at'
    ];

    protected $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>='
    ];

}