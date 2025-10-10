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
    Schema::create('profiles', function (Blueprint $t) {
      $t->id();
      $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
      $t->string('full_name')->nullable();
      $t->string('phone', 30)->nullable();
      $t->string('company')->nullable();
      $t->string('tax_id', 32)->nullable(); // NPWP optional
      $t->json('address_json')->nullable(); // {street, district, city, province, postal_code, notes}
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('profiles'); }
};
