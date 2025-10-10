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
    if (Schema::hasColumn('pop_specs', 'size')) {
      Schema::table('pop_specs', function (Blueprint $t) { $t->dropColumn('size'); });
    }
  }
  public function down(): void {
    Schema::table('pop_specs', function (Blueprint $t) {
      $t->string('size', 16)->default('A3'); // fallback
    });
  }
};
