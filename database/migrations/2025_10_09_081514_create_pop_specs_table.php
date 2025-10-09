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
        Schema::create('pop_specs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained('pop_products')->cascadeOnDelete();

            // Opsi inti POP
            $t->string('size', 16);            // A3+, A3, A4, dst
            $t->string('material', 32);        // Art Carton 230/260/310, HVS 80/100, dsb
            $t->enum('side', ['1s','2s']);
            $t->enum('lamination', ['none','doff','glossy'])->default('none');

            // Finishing POTONG khusus POP (bukan stiker)
            $t->enum('cutting', ['tanpa_potong','potong_lurus','potong_pola_die_cut'])
              ->default('tanpa_potong');

            // Kuantitas
            $t->unsignedInteger('min_qty')->default(50);
            $t->unsignedInteger('step_qty')->default(50);

            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['product_id','is_active','size','material','side','lamination','cutting'], 'pop_specs_idx');
        });
    }
    public function down(): void {
        Schema::dropIfExists('pop_specs');
    }
};
