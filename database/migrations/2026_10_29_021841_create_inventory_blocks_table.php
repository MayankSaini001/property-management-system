<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();

            $table->string('block_type')->default('maintenance'); // maintenance | owner_use

            // Both dates are included in the block.
            $table->date('start_date');
            $table->date('end_date');

            $table->string('reason', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'start_date', 'end_date']);
            $table->index(['property_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_blocks');
    }
};