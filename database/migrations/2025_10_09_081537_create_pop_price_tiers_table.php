<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('pop_price_tiers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('spec_id')->constrained('pop_specs')->cascadeOnDelete();
            $t->unsignedInteger('min_qty');          // threshold
            $t->decimal('unit_price', 12, 2);        // harga per lembar
            $t->timestamps();

            $t->index(['spec_id','min_qty']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('pop_price_tiers');
    }
};
