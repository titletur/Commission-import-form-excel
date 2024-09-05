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
        Schema::create('tb_store', function (Blueprint $table) {
            $table->id();
            $table->string('suppliercode');
            $table->string('store_id')->nullable();
            $table->string('store')->nullable();
            $table->string('type_store')->nullable();
            $table->string('status_store')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_store');
    }
};
