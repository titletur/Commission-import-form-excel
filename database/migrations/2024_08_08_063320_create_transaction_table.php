<?php

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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('report_code');
            $table->string('suppliercode');
            $table->string('suppliername');
            $table->string('business_format');
            $table->string('compare')->nullable();
            $table->string('store_id')->nullable();
            $table->string('store')->nullable();
            $table->string('as_of_month')->nullable();
            $table->string('as_of_year')->nullable();
            $table->date('last_year_compare_month')->nullable();
            $table->date('report_date')->nullable();
            $table->string('division')->nullable();
            $table->string('department')->nullable();
            $table->string('subdepartment')->nullable();
            $table->string('pro_Class')->nullable();
            $table->string('sub_pro_class')->nullable();
            $table->string('barcode')->nullable();
            $table->string('article')->nullable();
            $table->string('article_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('pro_model')->nullable();
            $table->string('type_product')->nullable();
            $table->decimal('sale_amt_ty', 15, 2)->nullable();
            $table->decimal('sale_amt_ty_vat', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->decimal('sale_price_vat', 15, 2)->nullable();
            $table->decimal('sale_amt_ly', 15, 2)->nullable();
            $table->decimal('sale_amt_var', 15, 2)->nullable();
            $table->decimal('sale_qty_ty', 15, 2)->nullable();
            $table->decimal('sale_qty_ly', 15, 2)->nullable();
            $table->decimal('sale_qty_var', 15, 2)->nullable();
            $table->decimal('stock_ty', 15, 2)->nullable();
            $table->decimal('stock_ly', 15, 2)->nullable();
            $table->decimal('stock_var', 15, 2)->nullable();
            $table->decimal('stock_qty_ty', 15, 2)->nullable();
            $table->decimal('stock_qty_ly', 15, 2)->nullable();
            $table->decimal('stock_qty_var', 15, 2)->nullable();
            $table->decimal('day_on_hand_ty', 15, 2)->nullable();
            $table->decimal('day_on_hand_ly', 15, 2)->nullable();
            $table->decimal('day_on_hand_diff', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
