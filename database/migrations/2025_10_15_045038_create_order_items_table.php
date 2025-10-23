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
    Schema::create('order_items', function (Blueprint $t) {
      $t->id();
      $t->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

      $t->string('product_type', 20); // 'pop' | 'mmt' | dll
      $t->unsignedBigInteger('product_id')->nullable();
      $t->string('product_name'); // snapshot nama

      $t->json('spec_snapshot')->nullable();
      $t->json('pricing_breakdown')->nullable();
      $t->string('note', 500)->nullable();

      $t->unsignedInteger('qty')->default(1);
      $t->unsignedBigInteger('unit_price'); // untuk mmt meteran: total line / qty (biasanya 1)
      $t->unsignedBigInteger('total_price');

      $t->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('order_items');
  }
};
