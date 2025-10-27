<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('merch_price_tiers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('variant_id')
              ->constrained('merch_variants')
              ->cascadeOnDelete();

            // qty minimum yang berlaku untuk tier ini
            // contoh:
            //   min_qty 1   -> 48000 /pcs
            //   min_qty 12  -> 45000 /pcs
            $t->unsignedInteger('min_qty');

            // harga per pcs untuk qty >= min_qty
            $t->unsignedInteger('unit_price');

            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('merch_price_tiers');
    }
};
