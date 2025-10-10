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
        Schema::create('carts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('session_id', 64)->nullable()->index();
            $t->enum('status', ['draft','locked'])->default('draft');
            $t->decimal('subtotal', 12, 2)->default(0);
            $t->decimal('discount_total', 12, 2)->default(0);
            $t->decimal('shipping_total', 12, 2)->default(0);
            $t->decimal('grand_total', 12, 2)->default(0);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('carts');
    }
};
