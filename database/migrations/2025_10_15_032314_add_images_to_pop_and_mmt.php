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
    Schema::table('pop_products', function (Blueprint $t) {
      if (!Schema::hasColumn('pop_products','thumbnail_url')) {
        $t->string('thumbnail_url')->nullable()->after('slug');
      }
      if (!Schema::hasColumn('pop_products','images_json')) {
        $t->json('images_json')->nullable()->after('thumbnail_url');
      }
    });

    Schema::table('mmt_products', function (Blueprint $t) {
      if (!Schema::hasColumn('mmt_products','thumbnail_url')) {
        $t->string('thumbnail_url')->nullable()->after('slug');
      }
      if (!Schema::hasColumn('mmt_products','images_json')) {
        $t->json('images_json')->nullable()->after('thumbnail_url');
      }
    });
  }
  public function down(): void {
    Schema::table('pop_products', function (Blueprint $t) {
      $t->dropColumn(['thumbnail_url','images_json']);
    });
    Schema::table('mmt_products', function (Blueprint $t) {
      $t->dropColumn(['thumbnail_url','images_json']);
    });
  }
};
