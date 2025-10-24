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
    Schema::create('sticker_price_tiers', function (Blueprint $t) {
      $t->id();
      $t->foreignId('spec_id')->constrained('sticker_specs')->cascadeOnDelete();
      $t->unsignedInteger('min_qty');      // breakpoint qty (1,10,50,100,...)
      $t->unsignedBigInteger('unit_price'); // rupiah per lembar
      $t->timestamps();

      $t->unique(['spec_id','min_qty']);
    });
  }
  public function down(): void { Schema::dropIfExists('sticker_price_tiers'); }
};
