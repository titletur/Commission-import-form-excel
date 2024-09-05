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
        Schema::create('tb_month', function (Blueprint $table) {
            $table->id();
            $table->string('month_th')->nullable();
            $table->string('short_th')->nullable();
            $table->string('month_en')->nullable();
            $table->string('short_en')->nullable();
            $table->string('var_month')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_month');
    }
};
