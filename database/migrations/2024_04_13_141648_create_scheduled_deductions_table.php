<?php

use App\Models\Loan;
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
        Schema::create('scheduled_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Loan::class, 'loan_id');
            $table->decimal('balance', 11, 2);
            $table->boolean('active')->default(true);
            $table->timestamp('due_date');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_deductions');
    }
};
