<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();

            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);

            $table->timestamps();

            $table->index(['unit_id', 'check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_items');
    }
};