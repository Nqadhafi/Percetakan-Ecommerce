<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\MMT\Models\MmtProduct;
use App\Domain\MMT\Models\MmtMaterialPrice;
use App\Domain\MMT\Models\MmtFinishing;
use App\Domain\MMT\Models\MmtPackagePrice;

class MmtJsonSeeder extends Seeder
{
    public function run(): void
    {
        // 1) MMT meteran
        $meter = MmtProduct::updateOrCreate(
            ['slug'=>'cetak-mmt-meteran'],
            ['name'=>'Cetak MMT/Banner Meteran','base_sku'=>'MMT-MTR','is_active'=>true,'kind'=>'meteran','min_area'=>1.00,'width_extra_m'=>0.2]
        );

        foreach ([
            ['material'=>'FLEX280','price'=>35000],
            ['material'=>'FLEX340','price'=>40000],
            ['material'=>'FRONTLIT510','price'=>45000],
        ] as $m) {
            MmtMaterialPrice::updateOrCreate(['product_id'=>$meter->id,'material'=>$m['material']], ['base_price_per_m2'=>$m['price'],'is_active'=>true]);
        }

        // finishing map JSON
        $finishSeed = [
          ['finishing'=>'none',                 'price_type'=>'flat','price_value'=>0],
          ['finishing'=>'simming',              'price_type'=>'flat','price_value'=>0],
          ['finishing'=>'simming_keling_pojok', 'price_type'=>'flat','price_value'=>0],
          ['finishing'=>'simming_keling_perm',  'price_type'=>'perimeter','price_value'=>3500], // tentatif
          ['finishing'=>'kolong_4',             'price_type'=>'flat','price_value'=>0],
          ['finishing'=>'kolong_8',             'price_type'=>'flat','price_value'=>0],
          ['finishing'=>'putihan_5',            'price_type'=>'flat','price_value'=>0],
          ['finishing'=>'putihan_10',           'price_type'=>'flat','price_value'=>0],
          ['finishing'=>'eyelet',               'price_type'=>'per_point','price_value'=>1500], // grommet per pc
        ];
        foreach ($finishSeed as $f) {
            MmtFinishing::updateOrCreate(
              ['product_id'=>$meter->id,'finishing'=>$f['finishing']],
              ['price_type'=>$f['price_type'],'price_value'=>$f['price_value'],'is_active'=>true]
            );
        }

        // 2) Tripod Banner (package)
        $tripod = MmtProduct::updateOrCreate(
            ['slug'=>'tripod-banner'],
            ['name'=>'Tripod Banner','base_sku'=>'TRI-BAN','is_active'=>true,'kind'=>'package','has_pole'=>true,'pole_price'=>25000,'pole_default_include'=>true]
        );

        foreach ([
            ['material'=>'ALBA_BOARD',   'size'=>'40x60', 'price'=>40000],
            ['material'=>'ALBA_BOARD',   'size'=>'60x80', 'price'=>60000],
            ['material'=>'STICKER_BOARD','size'=>'40x60', 'price'=>35000],
            ['material'=>'STICKER_BOARD','size'=>'60x80', 'price'=>55000],
        ] as $p) {
            MmtPackagePrice::updateOrCreate(
              ['product_id'=>$tripod->id,'material'=>$p['material'],'size'=>$p['size']],
              ['base_price'=>$p['price']]
            );
        }

        // 3) X-Banner (package)
        $xb = MmtProduct::updateOrCreate(
            ['slug'=>'x-banner'],
            ['name'=>'X-Banner','base_sku'=>'X-BAN','is_active'=>true,'kind'=>'package','has_pole'=>true,'pole_price'=>60000,'pole_default_include'=>true]
        );

        foreach ([
            ['material'=>'ALBATROS','size'=>'60x160','price'=>85000],
            ['material'=>'ALBATROS','size'=>'80x180','price'=>110000],
            ['material'=>'FLEX280', 'size'=>'60x160','price'=>80000],
            ['material'=>'FLEX280', 'size'=>'80x180','price'=>105000],
        ] as $p) {
            MmtPackagePrice::updateOrCreate(
              ['product_id'=>$xb->id,'material'=>$p['material'],'size'=>$p['size']],
              ['base_price'=>$p['price']]
            );
        }

        // 4) Roll Banner (package)
        $rb = MmtProduct::updateOrCreate(
            ['slug'=>'roll-banner'],
            ['name'=>'Roll Banner','base_sku'=>'ROL-BAN','is_active'=>true,'kind'=>'package','has_pole'=>false]
        );

        foreach ([
            ['material'=>'ALBATROS','size'=>'60x160','price'=>250000],
            ['material'=>'ALBATROS','size'=>'80x200','price'=>350000],
            ['material'=>'FLEX280', 'size'=>'60x160','price'=>240000],
            ['material'=>'FLEX280', 'size'=>'80x200','price'=>340000],
        ] as $p) {
            MmtPackagePrice::updateOrCreate(
              ['product_id'=>$rb->id,'material'=>$p['material'],'size'=>$p['size']],
              ['base_price'=>$p['price']]
            );
        }

        // 5) Mini X-Banner (package, tanpa pole)
        $mxb = MmtProduct::updateOrCreate(
            ['slug'=>'mini-x-banner'],
            ['name'=>'Mini X-Banner','base_sku'=>'MINI-X','is_active'=>true,'kind'=>'package','has_pole'=>false]
        );

        MmtPackagePrice::updateOrCreate(
          ['product_id'=>$mxb->id,'material'=>'AC260','size'=>'40x60'],
          ['base_price'=>25000]
        );
    }
}
