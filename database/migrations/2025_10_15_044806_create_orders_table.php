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
    Schema::create('orders', function (Blueprint $t) {
      $t->id();
      $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

      $t->string('code', 24)->unique(); // e.g. ORD-20251015-0001
      $t->string('customer_name');
      $t->string('customer_phone', 32);
      $t->text('customer_address');

      $t->string('payment_method', 20); // 'bank' | 'qris'
      $t->string('payment_proof_path')->nullable();

      $t->unsignedBigInteger('subtotal_amount'); // total item (sebelum adj)
      $t->unsignedBigInteger('discount_amount')->default(0);
      $t->unsignedBigInteger('shipping_amount')->default(0);
      $t->unsignedBigInteger('adjustment_amount')->default(0); // untuk min order, dsb
      $t->unsignedBigInteger('total_amount'); // grand total

      $t->enum('status', ['pending','paid','in_production','completed','cancelled'])->default('pending');

      $t->json('meta')->nullable(); // catatan checkout, dll
      $t->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('orders');
  }
};
