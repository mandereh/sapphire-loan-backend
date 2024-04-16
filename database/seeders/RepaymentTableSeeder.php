<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\PaymentMethod;
use App\Models\Repayment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RepaymentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loans = Loan::all();
        $paymentMethods = PaymentMethod::all();

        foreach ($loans as $loan) {
            Repayment::create([
                'loan_id' => $loan->id,
                'amount' => $loan->approved_amount / 10, // This is just an example, replace with your logic
                'reference' => 'REF' . str_pad($loan->id, 6, '0', STR_PAD_LEFT),
                'payment_method_id' => $paymentMethods->random()->id,
            ]);
        }
    }
}
