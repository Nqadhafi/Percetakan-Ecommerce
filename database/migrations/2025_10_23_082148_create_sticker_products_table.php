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
    Schema::create('sticker_products', function (Blueprint $t) {
      $t->id();
      $t->string('name');
      $t->string('slug')->unique();
      $t->boolean('is_active')->default(true);
      $t->string('thumbnail_url')->nullable();
      $t->json('images_json')->nullable(); // array URL/gambar
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('sticker_products'); }
};
