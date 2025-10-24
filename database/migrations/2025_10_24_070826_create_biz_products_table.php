<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('biz_products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();

            // flyer | brosur | kartu_nama
            $t->string('category', 20);

            // contoh: "1 Rim (500 lembar)", "1 Pack (100 kartu)"
            $t->string('unit_label', 50)->nullable();

            // harga dasar per unit (1 rim / 1 pack)
            $t->unsignedBigInteger('base_price');

            $t->string('thumbnail_url')->nullable();
            $t->json('images_json')->nullable();

            // JSON spek dasar: { size, paper, pcs_per_unit, ... }
            $t->json('spec_json')->nullable();

            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('biz_products');
    }
};
