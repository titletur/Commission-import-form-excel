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
        Schema::create('tb_main_commission', function (Blueprint $table) {
            $table->id();
            $table->string('store_id')->nullable();
            $table->string('type_store')->nullable();
            $table->string('as_of_month')->nullable();
            $table->string('as_of_year')->nullable();
            $table->decimal('sale_tv', 15, 2)->nullable(); //query tb_commission sum (sale_total_vat)  where type_product = "TV" And id_pc =id_pc And store_id =store_id
            $table->decimal('sale_av', 15, 2)->nullable(); //query tb_commission sum (sale_total_vat)  where type_product = "AV" And id_pc =id_pc And store_id =store_id
            $table->decimal('sale_ha', 15, 2)->nullable(); //query tb_commission sum (sale_total_vat)  where type_product = "HA" And id_pc =id_pc And store_id =store_id
            $table->decimal('sale_total', 15, 2)->nullable(); // sale_tv + sale_av + sale_ha
            $table->string('id_pc')->nullable(); //query tb_pc  
            $table->string('name_pc')->nullable();
            //query tb_pc
            $table->decimal('pc_salary', 15, 2)->nullable(); //query tb_pc
            $table->decimal('tarket', 15, 2)->nullable(); //query tb_pc
            $table->decimal('achieve', 15, 2)->nullable(); //(sale_tv + sale_av)*100/tarket 
            $table->decimal('normalcom_tv', 15, 2)->nullable(); //query sum tb_commission ((sale_amt_vat * com) /100)*sale_qty where type_product = "TV" And id_pc =id_pc And store_id =store_id
            $table->decimal('normalcom_av', 15, 2)->nullable(); //query sum tb_commission ((sale_amt_vat * com) /100)*sale_qty where type_product = "AV" And id_pc =id_pc And store_id =store_id
            $table->decimal('normalcom_ha', 15, 2)->nullable(); //query sum tb_commission ((sale_amt_vat * com) /100)*sale_qty where type_product = "HA" And id_pc =id_pc And store_id =store_id
            $table->decimal('unit_tv', 15, 2)->nullable(); //query sum sale_qty where type_product = "TV" And id_pc =id_pc And store_id =store_id
            $table->decimal('unit_av', 15, 2)->nullable(); // $table->string('type_pc')->nullable();query sum sale_qty where type_product = "AV" And id_pc =id_pc And store_id =store_id
            $table->decimal('unit_ha', 15, 2)->nullable(); //query sum sale_qty where type_product = "HA" And id_pc =id_pc And store_id =store_id
            $table->decimal('com_tv', 15, 2)->nullable(); //คำนวณตามเงื่อนไข
            $table->decimal('com_av', 15, 2)->nullable(); //คำนวณตามเงื่อนไข
            $table->decimal('com_ha', 15, 2)->nullable(); //คำนวณตามเงื่อนไข
            $table->decimal('pay_com', 15, 2)->nullable(); //com_tv + com_av + com_ha
            $table->decimal('extra_tv', 15, 2)->nullable();//extra ตามเงื่อนไข
            $table->decimal('extra_ha', 15, 2)->nullable();//extra ตามเงื่อนไข
            $table->decimal('net_com', 15, 2)->nullable(); // pay_com + extra_tv + extra_ha
            $table->decimal('advance_pay', 15, 2)->nullable(); //เบิกล่วงหน้า
            $table->decimal('other', 15, 2)->nullable(); //อื่นๆ
            $table->string('remark')->nullable();
            $table->decimal('net_pay', 15, 2)->nullable(); // net_com -advance_pay
            $table->decimal('dis_pay', 15, 2)->nullable(); // sale_total /net_com 
            
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
