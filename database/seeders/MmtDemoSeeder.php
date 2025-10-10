<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\MMT\Models\MmtProduct;
use App\Domain\MMT\Models\MmtMaterialPrice;
use App\Domain\MMT\Models\MmtFinishing;

class MmtDemoSeeder extends Seeder
{
    public function run(): void
    {
        $p = MmtProduct::firstOrCreate(
            ['slug'=>'banner-outdoor-standar'],
            ['name'=>'Banner Outdoor Standar','base_sku'=>'BN-STD','is_active'=>true,'min_area'=>1.00]
        );

        foreach ([
            ['material'=>'frontlit_280','base_price_per_m2'=>28000],
            ['material'=>'frontlit_340','base_price_per_m2'=>34000],
            ['material'=>'frontlit_440','base_price_per_m2'=>42000],
            ['material'=>'albatros','base_price_per_m2'=>55000],
        ] as $m) {
            MmtMaterialPrice::firstOrCreate(['product_id'=>$p->id,'material'=>$m['material']], ['base_price_per_m2'=>$m['base_price_per_m2']]);
        }

        foreach ([
            ['finishing'=>'ring','price_type'=>'perimeter','price_value'=>3500],
            ['finishing'=>'tali','price_type'=>'perimeter','price_value'=>2500],
            ['finishing'=>'pole','price_type'=>'flat','price_value'=>15000],
            ['finishing'=>'eyelet','price_type'=>'per_point','price_value'=>1500],
        ] as $f) {
            MmtFinishing::firstOrCreate(
                ['product_id'=>$p->id,'finishing'=>$f['finishing']],
                ['price_type'=>$f['price_type'],'price_value'=>$f['price_value']]
            );
        }
    }
}
