<?php

namespace Database\Factories;

use App\Models\LoanType;
use App\Models\Organization;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::all();
        $loanTypes = LoanType::all();
        $organizations = Organization::all();
        $states = State::all();
        return [
            'reference' => 'REF' . str_pad(6 + 1, 5, '0', STR_PAD_LEFT),
            'loan_type_id' => $loanTypes->random()->id,
            'organization_id' => $organizations->random()->id,
            'user_id' => $users->random()->id,
            'relationship_manager_id' => $users->random()->id,
            'amount' => rand(1000, 10000),
            'approved_amount' => rand(1000, 10000),
            'balance' => rand(1000, 10000),
            'penalty_accrued' => rand(0, 500),
            'penalty_waived' => rand(0, 500),
            'tenor' => rand(1, 12),
            'address' => '123 Main St',
            'city' => 'City',
            'zipcode' => '12345',
            'salary_account_number' => '0235012284',
            'bank_code' => '023',
            'state_id' => $states->random()->id,
            'reffered_by_id' => $users->random()->id,
            'approved_at' => now(),
            'status' => 'approved',
            'assesment_done' => true,
            'deduction_setup' => true,
            'failure_reason' => null,
            'mandate_reference' => '190008773515',
            'organization_name' => $this->faker->company,
        ];
    }
}
