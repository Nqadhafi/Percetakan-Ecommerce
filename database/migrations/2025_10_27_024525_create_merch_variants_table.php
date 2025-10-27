<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('merch_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('merch_product_id')
              ->constrained('merch_products')
              ->cascadeOnDelete();

            // code dikirim dari FE -> BE -> cart snapshot
            // contoh:
            //   "box_polos", "box_custom"
            //   "uk_4_4", "uk_5_8"
            //   "include_box", "tanpa_box"
            $t->string('code', 50);

            // label user-facing
            //   "Box Polos"
            //   "Ukuran 4.4 cm"
            //   "Include Box"
            $t->string('label', 100);

            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('merch_variants');
    }
};
