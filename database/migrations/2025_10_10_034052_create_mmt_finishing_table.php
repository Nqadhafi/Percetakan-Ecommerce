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
    Schema::create('mmt_finishing', function (Blueprint $t) {
      $t->id();
      $t->foreignId('product_id')->constrained('mmt_products')->cascadeOnDelete();
      $t->string('finishing', 24);                 // ring, lipatan, tali, pole, dll.
      $t->enum('price_type', ['perimeter','flat','per_point'])->default('flat');
      $t->decimal('price_value', 12, 2)->default(0); // per m keliling / flat / per titik
      $t->boolean('is_active')->default(true);
      $t->timestamps();
      $t->index(['product_id','finishing']);
    });
  }
  public function down(): void { Schema::dropIfExists('mmt_finishing'); }
};
