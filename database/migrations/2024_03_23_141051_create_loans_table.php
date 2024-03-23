<?php

use App\Models\Bank;
use App\Models\LoanType;
use App\Models\Organization;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->uniqid('LO');
            $table->foreignIdFor(LoanType::class, 'loan_type_id');
            $table->foreignIdFor(Organization::class, 'organization_id');
            $table->foreignIdFor(User::class, 'user_id');
            $table->foreignId(User::class, 'relationship_manager');
            $table->decimal('amount', 11, 2);
            $table->decimal('approved_amount', 11, 2)->default(0);
            $table->decimal('balance', 11, 2)->default(0);
            $table->decimal('penalty_accrued', 11, 2)->default(0);
            $table->decimal('penalty_waived', 11, 2)->default(0);
            $table->tinyInteger('tenor');
            $table->string('address');
            $table->string('city');
            $table->string('zipcode', 10);
            $table->string('salary_account_number');
            $table->foreignIdFor(Bank::class, 'bank_id');
            $table->foreignIdFor(State::class, 'state_id');
            $table->foreignId(User::class, 'reffered_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('status');
            $table->boolean('assesmentDone')->default(false);
            $table->boolean('deductionSetup')->default(false);
            $table->text('failure_reason');
            $table->string('mandate_reference')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
