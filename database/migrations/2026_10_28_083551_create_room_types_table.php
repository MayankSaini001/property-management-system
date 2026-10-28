<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->unsignedTinyInteger('max_adults')->default(2);
            $table->unsignedTinyInteger('max_children')->default(0);
            $table->unsignedSmallInteger('capacity')->default(2);

            $table->string('status')->default('active'); // active | inactive

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'name']);
            $table->index(['company_id', 'property_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};