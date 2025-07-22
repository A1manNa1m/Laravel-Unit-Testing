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
}
