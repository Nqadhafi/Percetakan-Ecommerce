<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('merch_products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();

            // contoh: "Tumblr Kancing + Custom UV", "Pin Peniti", "Mug Custom 1 Sisi"
            $t->string('variant_label', 100)
              ->nullable(); // e.g. "Packaging", "Ukuran", "Pilihan Box"

            // minimal pemesanan (1 untuk tumbler/mug, 5 untuk pin/ganci)
            $t->unsignedInteger('min_order_qty')->default(1);

            $t->string('thumbnail_url')->nullable();
            $t->json('images_json')->nullable();

            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('merch_products');
    }
};
