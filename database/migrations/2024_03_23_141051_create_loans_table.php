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
            $table->string('reference')->unique();
            $table->foreignIdFor(LoanType::class, 'loan_type_id');
            $table->foreignIdFor(Organization::class, 'organization_id');
            $table->foreignIdFor(User::class, 'user_id');
            $table->foreignIdFor(User::class, 'relationship_manager_id')->nullable();
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
            $table->string('bank_code');
            $table->foreignIdFor(State::class, 'state_id');
            $table->foreignIdFor(User::class, 'reffered_by_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('status');
            $table->boolean('assesment_done')->default(false);
            $table->boolean('deduction_setup')->default(false);
            $table->text('failure_reason')->nullable();
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
