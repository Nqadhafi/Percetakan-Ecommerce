<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\POP\Models\PopProduct;
use App\Domain\POP\Models\PopSpec;
use App\Domain\POP\Models\PopPriceTier;
use Illuminate\Support\Str;

class PopDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Produk: Cetak Art Carton A3+
        $p = PopProduct::firstOrCreate(
            ['slug' => 'cetak-art-carton-a3-plus'],
            ['name' => 'Cetak Art Carton A3+', 'base_sku' => 'AC-A3P', 'is_active' => true]
        );

        $specs = [
            ['size'=>'A3+','material'=>'Art Carton 260','side'=>'1s','lamination'=>'none','cutting'=>'tanpa_potong','min_qty'=>50,'step_qty'=>50],
            ['size'=>'A3+','material'=>'Art Carton 260','side'=>'2s','lamination'=>'none','cutting'=>'potong_lurus','min_qty'=>50,'step_qty'=>50],
            ['size'=>'A3+','material'=>'Art Carton 310','side'=>'1s','lamination'=>'doff','cutting'=>'potong_pola_die_cut','min_qty'=>50,'step_qty'=>50],
        ];

        foreach ($specs as $s) {
            $spec = PopSpec::firstOrCreate(
                ['product_id'=>$p->id] + $s
            );

            // Tier contoh
            $tiers = [
                ['min_qty'=>50,  'unit_price'=>3500],
                ['min_qty'=>100, 'unit_price'=>3200],
                ['min_qty'=>200, 'unit_price'=>3000],
            ];
            foreach ($tiers as $t) {
                PopPriceTier::firstOrCreate(['spec_id'=>$spec->id,'min_qty'=>$t['min_qty']], ['unit_price'=>$t['unit_price']]);
            }
        }
    }
}
