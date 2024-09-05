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
        Schema::create('tb_sale', function (Blueprint $table) {
            $table->id('id_sale')->unsigned;
            $table->string('code_sale');
            $table->string('name_sale');
            $table->decimal('target', 15, 2);
            $table->decimal('base_com', 15, 2);
            $table->string('status_sale')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_sale');
    }
};
