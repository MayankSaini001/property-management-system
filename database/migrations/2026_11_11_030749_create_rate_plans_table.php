<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();

            $table->string('name', 120);
            $table->string('meal_plan', 30)->default('room_only'); // room_only | breakfast | half_board | full_board
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');       // active | inactive
            $table->boolean('is_default')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'name']);
            $table->index(['company_id', 'property_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_plans');
    }
};