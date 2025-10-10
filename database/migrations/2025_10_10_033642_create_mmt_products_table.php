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
    Schema::create('mmt_products', function (Blueprint $t) {
      $t->id();
      $t->string('name');
      $t->string('slug')->unique();
      $t->string('base_sku')->nullable();
      $t->boolean('is_active')->default(true);
      $t->enum('area_unit', ['m2'])->default('m2');
      $t->decimal('min_area', 8, 2)->default(1.00);  // minimal dihitung
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('mmt_products'); }
};
