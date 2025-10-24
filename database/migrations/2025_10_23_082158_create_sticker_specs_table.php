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
    Schema::create('sticker_specs', function (Blueprint $t) {
      $t->id();
      $t->foreignId('product_id')->constrained('sticker_products')->cascadeOnDelete();
      $t->string('material', 32);              // vinyl_white | vinyl_clear | hologram | kraft | ...
      $t->boolean('supports_lamination')->default(true);
      $t->unsignedInteger('min_qty')->default(1); // hint UI saja
      $t->boolean('is_active')->default(true);
      $t->timestamps();

      $t->index(['product_id','material']);
    });
  }
  public function down(): void { Schema::dropIfExists('sticker_specs'); }
};
