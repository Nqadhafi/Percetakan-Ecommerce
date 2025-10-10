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
    Schema::table('mmt_products', function (Blueprint $t) {
      if (!Schema::hasColumn('mmt_products', 'kind')) {
        $t->enum('kind', ['meteran','package'])->default('meteran')->after('is_active');
      }
      if (!Schema::hasColumn('mmt_products', 'width_extra_m')) {
        $t->decimal('width_extra_m', 8, 3)->default(0.0)->after('min_area');
      }
      if (!Schema::hasColumn('mmt_products', 'has_pole')) {
        $t->boolean('has_pole')->default(false)->after('width_extra_m');
        $t->decimal('pole_price', 12, 2)->default(0)->after('has_pole');
        $t->boolean('pole_default_include')->default(true)->after('pole_price');
      }
    });

    if (!Schema::hasTable('mmt_package_prices')) {
      Schema::create('mmt_package_prices', function (Blueprint $t) {
        $t->id();
        $t->foreignId('product_id')->constrained('mmt_products')->cascadeOnDelete();
        $t->string('material', 32);
        $t->string('size', 16);
        $t->decimal('base_price', 12, 2);
        $t->timestamps();
        $t->unique(['product_id','material','size']);
      });
    }
  }

  public function down(): void {
    Schema::dropIfExists('mmt_package_prices');
    Schema::table('mmt_products', function (Blueprint $t) {
      $t->dropColumn(['kind','width_extra_m','has_pole','pole_price','pole_default_include']);
    });
  }
};
