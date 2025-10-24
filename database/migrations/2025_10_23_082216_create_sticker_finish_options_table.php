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
    Schema::create('sticker_finish_options', function (Blueprint $t) {
      $t->id();
      $t->foreignId('product_id')->nullable()->constrained('sticker_products')->nullOnDelete();
      $t->string('material', 32)->nullable();   // override per material (opsional)
      $t->enum('kind', ['lamination','finish']);
      $t->string('code', 32);                   // none|doff|glossy|straight_cut|kiss_cut|die_cut
      $t->string('label')->nullable();
      $t->enum('pricing_mode', ['per_sheet','flat_job'])->default('per_sheet');
      $t->unsignedBigInteger('amount')->default(0); // rupiah
      $t->boolean('is_active')->default(true);
      $t->timestamps();

      $t->index(['product_id','material','kind','code']);
    });
  }
  public function down(): void { Schema::dropIfExists('sticker_finish_options'); }
};
