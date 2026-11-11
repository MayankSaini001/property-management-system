<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();

            // Hotels are priced per room type, villas per unit. Only one of these is set.
            $table->foreignId('room_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->cascadeOnDelete();

            $table->decimal('weekday_price', 12, 2);
            $table->decimal('weekend_price', 12, 2);

            $table->timestamps();

            $table->unique(['rate_plan_id', 'room_type_id']);
            $table->unique(['rate_plan_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_plan_prices');
    }
};