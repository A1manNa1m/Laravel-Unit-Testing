<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $Paidmethod = $this->faker->randomElement(['CC','DC','FPX']); //creditcard, debitcard, OnlineBanking

        return [
            'customer_id'=>Customer::factory(),
            'invoice_id'=>Invoice::factory(),
            'amount'=>$this->faker->numberBetween(100,20000),
            'paid_method'=>$Paidmethod,
            'paid_at'=>$this->faker->dateTimeThisDecade()
        ];
    }
}
