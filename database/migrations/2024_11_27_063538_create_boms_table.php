<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->string('query')->nullable();
            $table->integer('qty')->nullable();
            $table->boolean('is_match')->default(1);
            $table->string('part')->nullable();
            $table->string('part_description')->nullable();
            $table->string('description')->nullable();
            $table->string('schematic_reference')->nullable();
            $table->string('internal_part_no')->nullable();
            $table->string('lifecycle')->nullable();
            $table->string('lead_time')->nullable();
            $table->string('rohs')->nullable();
            $table->string('digi_key')->nullable();
            $table->string('mouser')->nullable();
            $table->string('newark')->nullable();
            $table->string('online_component')->nullable();
            $table->string('rs_component')->nullable();
            $table->string('distributor')->nullable();
            $table->string('unit_price')->nullable();
            $table->decimal('line_total',4)->nullable();
            $table->decimal('bacth_total',4)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boms');
    }
};
