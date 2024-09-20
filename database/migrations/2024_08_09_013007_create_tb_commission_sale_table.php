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
        Schema::create('tb_commission_sale', function (Blueprint $table) {
            $table->id();
            $table->integer('id_sale')->unsigned(); // เปลี่ยนเป็น integer
            $table->string('as_of_month', 2);
            $table->string('as_of_year', 4);
            $table->decimal('target', 15, 2);
            $table->decimal('achieve', 15, 2);
            $table->decimal('base_com', 15, 2);
            $table->decimal('com_sale', 15, 2);
            $table->decimal('add_total', 15, 2)->nullable();
            $table->decimal('sale_tv', 15, 2);
            $table->decimal('unit_tv', 15, 2);
            $table->decimal('sale_av', 15, 2);
            $table->decimal('unit_av', 15, 2);
            $table->decimal('sale_ha', 15, 2);
            $table->decimal('unit_ha', 15, 2);
            $table->decimal('sale_out', 15, 2);

            $table->decimal('extra_sale_out', 15, 2)->nullable();
            $table->decimal('extra_unit', 15, 2)->nullable();
            $table->decimal('extra_avg', 15, 2)->nullable();
            $table->decimal('other', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->string('remark', 500);
            $table->timestamps();
        });
        

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_commission_sale');
    }
};
