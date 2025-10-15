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
    Schema::table('cart_items', function (Blueprint $t) {
      $t->string('note', 500)->nullable()->after('spec_snapshot');
    });
  }
  public function down(): void {
    Schema::table('cart_items', function (Blueprint $t) {
      $t->dropColumn('note');
    });
  }
};
