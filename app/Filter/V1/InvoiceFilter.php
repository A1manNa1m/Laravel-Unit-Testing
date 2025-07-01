<?php

namespace App\Filter\V1;

use Illuminate\Http\Request;
use App\Filter\ApiFilter;

class InvoiceFilter extends ApiFilter {

    protected $safeParams = [
        'customer_id' => ['eq'],
        'amount' => ['eq','lt','lte','gt','gte'],
        'status' => ['eq','ne'],
        'billed_date' => ['eq','lt','lte','gt','gte'],
        'paid_date' => ['eq','lt','lte','gt','gte']
    ];

    protected $columnMap = [
        'customerId' => 'customer_id',
        'billedDate' => 'billed_date',
        'PaidDate' => 'paid_date'
    ];

    protected $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
        'ne' => '!='
    ];
}