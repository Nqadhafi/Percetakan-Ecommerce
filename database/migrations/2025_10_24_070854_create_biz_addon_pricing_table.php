<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('biz_addon_pricing', function (Blueprint $t) {
            $t->id();
            $t->foreignId('biz_product_id')->constrained('biz_products')->cascadeOnDelete();

            // contoh: two_side, fold_half, fold_tri, lamination_doff, dll
            $t->string('code', 40);
            $t->string('label', 100)->nullable();

            // biaya tambahan per 1 unit paket (per rim / per pack)
            $t->unsignedBigInteger('amount_per_unit')->default(0);

            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('biz_addon_pricing');
    }
};
