<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Http\Requests\V1\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaymentCollection;
use App\Http\Resources\V1\PaymentResource;
use App\Filter\V1\PaymentFilter;
use App\Models\Invoice;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->tokenCan('read')) {
        abort(403, 'Unauthorized');
        }

        $filter = new PaymentFilter();
        $queryItems = $filter->transform($request);

        if(count($queryItems) == 0){
            return new PaymentCollection(Payment::paginate());
        }else{
            $payment = Payment::where($queryItems)->paginate();
            return new PaymentCollection($payment->appends($request->query()));
        }  

        // $payments = Payment::all();
        // return new PaymentCollection($payments);

        /**
         * Simple way to display data
        */
        // return Payment::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        $validated =$request ->validated();
        $invoice = Invoice::findOrFail($validated['invoice_id']);

        $totalPaid = $invoice->payments()->sum('amount');
        $newTotal =  $totalPaid + $validated['amount'];

        // Update invoice status based on payment amount
        if ($newTotal == $invoice->amount) {
            $invoice->status = 'FP'; // Full Paid
            $invoice->paid_date = now();
        } elseif ($newTotal > $invoice->amount) {
            $invoice->status = 'OP'; // Over Paid
            $invoice->paid_date = now();
        } elseif ($newTotal > 0) {
            $invoice->status = 'HP'; // Half Paid
            $invoice->paid_date = null;
        } else {
            $invoice->status = 'B'; // Still billed
            $invoice->paid_date = null;
        }

        $invoice->save();

        // Store the new payment
        $payment = Payment::create($validated);
        return new PaymentResource($payment);

        // return new PaymentResource(Payment::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        return new PaymentResource($payment);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }
}
