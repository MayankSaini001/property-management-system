<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->restrictOnDelete();

            $table->string('booking_reference', 30)->unique();
            $table->string('status')->default('confirmed'); // confirmed | checked_in | checked_out | cancelled | no_show
            $table->string('source')->default('direct');    // direct | walk_in | phone | email | other

            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);

            $table->string('currency', 3);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'property_id', 'status']);
            $table->index(['property_id', 'check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};