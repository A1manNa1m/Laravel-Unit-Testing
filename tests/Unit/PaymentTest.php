<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\Payment;
use Mockery;
use PHPUnit\Framework\TestCase;
use App\Services\PaymentService;
use App\Repositories\InvoiceRepositoryInterface;
use App\Repositories\PaymentRepositoryInterface;

class PaymentTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @dataProvider paymentStatusProvider
     */
    public function test_store_sets_correct_invoice_status(int $invoiceAmount,int $alreadyPaid,int $newPayment,string $expectedStatus,bool $shouldSetPaidDate) {
        $invoiceId = 1;

        // Existing invoice (status can be anything initially)
        $invoice = new Invoice([
            'id' => $invoiceId,
            'amount' => $invoiceAmount,
            'status' => '', // assume partially paid before
            'paid_date' => null,
        ]);

        // Mock InvoiceRepository
        $invRepo = Mockery::mock(InvoiceRepositoryInterface::class);
        $invRepo->shouldReceive('findOrFail')->with($invoiceId)->andReturn($invoice);
        $invRepo->shouldReceive('sumPayments')->with($invoiceId)->andReturn($alreadyPaid);
        $invRepo->shouldReceive('save')->with(Mockery::on(function($inv) use ($expectedStatus, $shouldSetPaidDate) {
            // Check if status is correct
            if ($inv->status !== $expectedStatus) {
                return false;
            }
            // Check if paid_date is set or null correctly
            if ($shouldSetPaidDate && is_null($inv->paid_date)) {
                return false;
            }
            if (!$shouldSetPaidDate && !is_null($inv->paid_date)) {
                return false;
            }
            return true;
        }))->once();

        // Mock PaymentRepository
        $payment = new Payment([
            'id' => 123,
            'invoice_id' => $invoiceId,
            'amount' => $newPayment,
        ]);

        $payRepo = Mockery::mock(PaymentRepositoryInterface::class);
        $payRepo->shouldReceive('create')
                ->with(['invoice_id' => $invoiceId, 'amount' => $newPayment])
                ->once()
                ->andReturn($payment);

        // Service
        $service = new PaymentService($invRepo, $payRepo);
        $result  = $service->store($invoiceId, $newPayment);

        $this->assertSame($payment, $result);
    }

    /**
     * Provides scenarios for each invoice status outcome.
     *
     * Each case: [invoiceAmount, alreadyPaid, newPayment, expectedStatus, shouldSetPaidDate]
     */
    public function paymentStatusProvider(): array
    {
        return [
            // Case 1: Exactly full paid (FP)
            [100, 50, 50, 'FP', true],

            // Case 2: Overpaid (OP)
            [100, 100, 10, 'OP', true],

            // Case 3: Still partially paid (HP)
            [100, 20, 30, 'HP', false],

            // Case 4: No payments (Billed only)
            [100, 0, 0, 'B', false],
        ];
    }

    /**
     * @dataProvider updateStatusProvider
     */
    public function test_update_sets_correct_invoice_status(
        int $invoiceAmount,
        int $otherPayments,   // sum of all other payments
        int $oldPayment,      // amount on the payment being updated
        int $newAmount,       // new amount for this payment
        string $expectedStatus,
        bool $shouldSetPaidDate
    ) {
        $invoiceId = 1;
        $paymentId = 99;

        // The payment being updated (with its old amount)
        $payment = new Payment([
            'id' => $paymentId,
            'invoice_id' => $invoiceId,
            'amount' => $oldPayment,
        ]);

        // The invoice (total due)
        $invoice = new Invoice([
            'id'        => $invoiceId,
            'amount'    => $invoiceAmount,
            'status'    => 'HP',  // assume partially paid before
            'paid_date' => null,
        ]);

        // Mock InvoiceRepository
        $invRepo = Mockery::mock(InvoiceRepositoryInterface::class);
        $invRepo->shouldReceive('findOrFail')->with($invoiceId)->andReturn($invoice);
        // sumPayments() includes the old payment, so we subtract it later in logic
        $invRepo->shouldReceive('sumPayments')->with($invoiceId)->andReturn($otherPayments + $oldPayment);
        $invRepo->shouldReceive('save')->with(Mockery::on(function($inv) use ($expectedStatus, $shouldSetPaidDate) {
            if ($inv->status !== $expectedStatus) {
                return false;
            }
            if ($shouldSetPaidDate && is_null($inv->paid_date)) {
                return false;
            }
            if (!$shouldSetPaidDate && !is_null($inv->paid_date)) {
                return false;
            }
            return true;
        }))->once();

        // Mock PaymentRepository
        $payRepo = Mockery::mock(PaymentRepositoryInterface::class);
        $payRepo->shouldReceive('findOrFail')->with($paymentId)->andReturn($payment);
        $payRepo->shouldReceive('update')->with($payment, ['amount' => $newAmount])->once()->andReturn(true);

        // Run the service
        $svc = new PaymentService($invRepo, $payRepo);
        $result = $svc->update($paymentId, ['amount' => $newAmount]);

        $this->assertSame($payment, $result);
    }

    /**
     * Provides cases for each status outcome during update.
     *
     * Each entry: [invoiceAmount, otherPayments, oldPayment, newAmount, expectedStatus, shouldSetPaidDate]
     */
    public function updateStatusProvider(): array
    {
        return [
            // Case 1: Full Paid (FP) - Total = 100
            [100, 30, 20, 70, 'FP', true],  
            // 30 (others) + 70 (replacing 20) = 100

            // Case 2: Overpaid (OP) - Total = 130
            [100, 80, 20, 50, 'OP', true],  
            // 80 (others) + 50 (replacing 20) = 130

            // Case 3: Half Paid (HP) - Total = 60
            [100, 20, 10, 40, 'HP', false], 
            // 20 (others) + 40 (replacing 10) = 60

            // Case 4: Still Billed (B) - Total = 0
            [100, 0, 0, 0, 'B', false],     
            // 0 (no payments at all)
        ];
    }
}
