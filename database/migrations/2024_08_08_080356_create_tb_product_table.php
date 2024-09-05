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
        Schema::create('tb_product', function (Blueprint $table) {
            $table->id();
            $table->string('suppliercode')->nullable();
            $table->string('division')->nullable();
            $table->string('department')->nullable();
            $table->string('subdepartment')->nullable();
            $table->string('pro_class')->nullable();
            $table->string('sub_pro_class')->nullable();
            $table->string('barcode')->nullable();
            $table->string('article')->nullable();
            $table->string('article_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('pro_model')->nullable();
            $table->string('type_product')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('price_vat', 15, 2)->nullable();
            $table->decimal('com', 15, 2)->nullable();
            $table->string('status_product')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_product');
    }
};
