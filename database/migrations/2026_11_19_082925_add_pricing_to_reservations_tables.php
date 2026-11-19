<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('rate_plan_id')
                ->nullable()
                ->after('guest_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('reservation_items', function (Blueprint $table) {
            $table->decimal('total_amount', 12, 2)->default(0)->after('children');
        });
    }

    public function down(): void
    {
        Schema::table('reservation_items', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rate_plan_id');
        });
    }
};