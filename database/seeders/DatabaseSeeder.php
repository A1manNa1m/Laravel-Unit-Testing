<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // $this->call([
        //     CustomerSeeder::class
        // ]);
        // Create 100 customers
        Customer::factory()
        ->count(100)
        ->create()
        ->each(function ($customer) {
            // For each customer, create 1–5 invoices
            Invoice::factory()
                ->count(rand(1, 5))
                ->create([
                    'customer_id' => $customer->id,
                ])
                ->each(function ($invoice) use ($customer) {

                    switch ($invoice->status) {
                        case 'FP': // Full Paid
                            Payment::factory()->create([
                                'customer_id' => $customer->id,
                                'invoice_id' => $invoice->id,
                                'amount' => $invoice->amount,
                                'paid_at' => $invoice->paid_date ?? now(),
                            ]);
                            break;

                        case 'HP': // Half Paid
                            Payment::factory()->create([
                                'customer_id' => $customer->id,
                                'invoice_id' => $invoice->id,
                                'amount' => $invoice->amount / 2,
                                'paid_at' => now(),
                            ]);
                            break;

                        case 'OP': // Over Paid
                            Payment::factory()->create([
                                'customer_id' => $customer->id,
                                'invoice_id' => $invoice->id,
                                'amount' => $invoice->amount + rand(10, 100),
                                'paid_at' => now(),
                            ]);
                            break;

                        case 'B': // Billed (not yet paid)
                        case 'V': // Void (should not have payment)
                        default:
                            // No payment
                            break;
                    }
                });
        });
    }
}
