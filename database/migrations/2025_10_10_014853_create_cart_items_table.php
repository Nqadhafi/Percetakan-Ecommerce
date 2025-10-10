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
        Schema::create('cart_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $t->enum('product_type', ['pop','mmt','sticker'])->index();
            $t->unsignedBigInteger('product_id');
            $t->string('name');
            $t->unsignedInteger('qty');
            $t->decimal('unit_price', 12, 2);
            $t->decimal('total_price', 12, 2);
            $t->json('spec_snapshot');
            $t->json('pricing_breakdown');
            $t->timestamps();

            $t->index(['product_type','product_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('cart_items');
    }
};
