<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();

            // Hotel rooms belong to a room type; villa units can be without one.
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('unit_type')->default('room'); // room | villa

            $table->unsignedTinyInteger('max_adults')->default(2);
            $table->unsignedTinyInteger('max_children')->default(0);
            $table->unsignedSmallInteger('capacity')->default(2);

            $table->string('status')->default('available'); // available | out_of_service | maintenance
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'name']);
            $table->index(['company_id', 'property_id']);
            $table->index(['property_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};