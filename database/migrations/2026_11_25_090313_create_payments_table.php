<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();

            $table->string('receipt_number', 30)->unique();
            $table->string('type', 10)->default('payment'); // payment | refund (refunds come in the next step)

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->string('method', 20);                   // cash | upi | card | bank_transfer | other
            $table->date('paid_on');

            $table->string('reference', 100)->nullable();
            $table->string('notes', 500)->nullable();

            // A wrong payment is never deleted, it is voided with a reason.
            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason', 500)->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['company_id', 'paid_on']);
            $table->index(['reservation_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};