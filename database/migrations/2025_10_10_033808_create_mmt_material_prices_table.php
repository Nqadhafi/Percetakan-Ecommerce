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
    Schema::create('mmt_material_prices', function (Blueprint $t) {
      $t->id();
      $t->foreignId('product_id')->constrained('mmt_products')->cascadeOnDelete();
      $t->string('material', 32); // frontlit_280/340/440, albatros, dsb
      $t->decimal('base_price_per_m2', 12, 2);
      $t->boolean('is_active')->default(true);
      $t->timestamps();
      $t->unique(['product_id','material']);
    });
  }
  public function down(): void { Schema::dropIfExists('mmt_material_prices'); }
};
