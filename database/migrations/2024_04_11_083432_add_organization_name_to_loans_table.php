<?php

use App\Models\Organization;
use App\Models\Product;
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
        Schema::table('loans', function (Blueprint $table) {
            $table->string('organization_name');
            $table->foreignIdFor(Product::class, 'product_id')->nullable();
            $table->string('zipcode')->nullable()->change();
            $table->foreignIdFor(Organization::class, 'organization_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('organization_name');
            $table->dropColumn('product_id');
            $table->string('zipcode')->change();
            $table->foreignIdFor(Organization::class, 'organization_id')->change();
        });
    }
};
