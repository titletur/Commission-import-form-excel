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
        Schema::create('tb_commission', function (Blueprint $table) {
            $table->id();
            $table->string('suppliercode');
            $table->string('store_id')->nullable();
            $table->string('type_store')->nullable();
            $table->string('as_of_month')->nullable();
            $table->string('as_of_year')->nullable();
            $table->string('pro_model')->nullable();
            $table->string('type_product')->nullable();
            $table->decimal('sale_amt', 15, 2)->nullable();
            $table->decimal('sale_amt_vat', 15, 2)->nullable();
            $table->decimal('sale_qty', 15, 2)->nullable();
            $table->string('id_pc')->nullable();
            $table->string('type_pc')->nullable();
            $table->string('com')->nullable();
            // $table->decimal('pc_salary', 15, 2)->nullable();
            // $table->decimal('tarket', 15, 2)->nullable();
            // $table->decimal('achieve', 15, 2)->nullable();
            // $table->decimal('normalcom_tv', 15, 2)->nullable();
            // $table->decimal('com_tv', 15, 2)->nullable();
            // $table->decimal('com_ha', 15, 2)->nullable();
            // $table->decimal('pay_com', 15, 2)->nullable();
            // $table->decimal('extra', 15, 2)->nullable();
            // $table->decimal('net_com', 15, 2)->nullable();
            // $table->decimal('advance_pay', 15, 2)->nullable();
            // $table->decimal('net_pay', 15, 2)->nullable();
            // $table->decimal('dis_pay', 15, 2)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_commission');
    }
};
