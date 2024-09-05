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
        Schema::create('tb_pc', function (Blueprint $table) {
            $table->id();
            $table->string('store_id')->nullable();
            $table->string('type_store')->nullable();
            $table->string('code_pc')->nullable();
            $table->string('name_pc')->nullable();
            $table->string('type_pc')->nullable();
            $table->decimal('tarket', 15, 2)->nullable();
            $table->decimal('salary', 15, 2)->nullable();
            $table->string('status_pc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_pc');
    }
};
