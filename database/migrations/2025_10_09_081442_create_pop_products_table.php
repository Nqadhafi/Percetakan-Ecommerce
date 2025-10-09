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
        Schema::create('pop_products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('base_sku')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('pop_products');
    }
};
