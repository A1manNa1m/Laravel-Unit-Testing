<?php

namespace App\Http\Controllers\Unit;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;
use App\Http\Requests\V1\StorePaymentRequest;
use App\Http\Requests\V1\UpdatePaymentRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaymentCollection;
use App\Http\Resources\V1\PaymentResource;
use App\Http\Requests\V1\BulkStorePaymentRequest;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request, PaymentService $svc)
    {
        $this->authorize('create', Payment::class);

        $payment = $svc->store(
            $request->validated('invoice_id'),
            $request->validated('amount'),
        );

        return new PaymentResource($payment);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment, PaymentService $svc)
    {
        $this->authorize('update', $payment);

        $updatedPayment = $svc->update($payment->id, $request->validated());

        return response()->json(['message' => 'Payment updated successfully', 'payment' => $updatedPayment]);
    }

    public function destroy(Payment $payment, PaymentService $svc)
    {
        $this->authorize('delete', $payment);

        $svc->destroy($payment->id);

        return response()->json([
            'message' => 'Payment deleted successfully'
        ]);
    }

    /**
     * Store a bulk payment resource in storage.
     */
    // public function bulkStorePayment(BulkStorePaymentRequest $request)
    // {
    //     $validatedPayments = $request->validated();  // This is an array of payments

    //     $createdPayments = [];

    //     foreach ($validatedPayments as $paymentData) {
    //         // 1. Find invoice for each payment
    //         $invoice = Invoice::findOrFail($paymentData['invoice_id']);

    //         // 2. Calculate new total payments for this invoice
    //         $totalPaid = $invoice->payments()->sum('amount') + $paymentData['amount'];

    //         // 3. Update invoice status
    //         if ($totalPaid == $invoice->amount) {
    //             $invoice->status = 'FP'; // Full Paid
    //             $invoice->paid_date = now();
    //         } elseif ($totalPaid > $invoice->amount) {
    //             $invoice->status = 'OP'; // Over Paid
    //             $invoice->paid_date = now();
    //         } elseif ($totalPaid > 0) {
    //             $invoice->status = 'HP'; // Half Paid
    //             $invoice->paid_date = null;
    //         } else {
    //             $invoice->status = 'B'; // Billed
    //             $invoice->paid_date = null;
    //         }

    //         $invoice->save();

    //         // 4. Create the payment
    //         $payment = Payment::create($paymentData);
    //         $createdPayments[] = $payment;
    //     }

    //     // 5. Return all created payments as a resource collection
    //     return PaymentResource::collection($createdPayments);

    // }

}
