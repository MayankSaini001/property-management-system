<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();

            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('note', 500)->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['reservation_id', 'id']);
        });

        // Reservations that already exist get a starting history entry.
        foreach (DB::table('reservations')->get() as $row) {
            DB::table('reservation_status_histories')->insert([
                'company_id' => $row->company_id,
                'reservation_id' => $row->id,
                'old_status' => null,
                'new_status' => 'confirmed',
                'note' => 'Reservation created',
                'changed_by' => $row->created_by,
                'created_at' => $row->created_at,
                'updated_at' => $row->created_at,
            ]);

            if ($row->status !== 'confirmed') {
                DB::table('reservation_status_histories')->insert([
                    'company_id' => $row->company_id,
                    'reservation_id' => $row->id,
                    'old_status' => 'confirmed',
                    'new_status' => $row->status,
                    'note' => $row->cancellation_reason,
                    'changed_by' => $row->created_by,
                    'created_at' => $row->updated_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_status_histories');
    }
};