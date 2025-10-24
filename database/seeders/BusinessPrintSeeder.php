<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\BusinessPrint\Models\BizProduct;
use App\Domain\BusinessPrint\Models\BizAddonPricing;

class BusinessPrintSeeder extends Seeder
{
    public function run(): void
    {
        /*
         |--------------------------------------------------------------------------
         | 1. FLYER A5 ART PAPER 150GSM (1 Rim / 500 lembar)
         |--------------------------------------------------------------------------
         | Addon yang tersedia:
         | - two_side (Cetak 2 sisi)
         |
         | Tidak ada lipatan. Tidak ada laminasi.
         */
        $flyer = BizProduct::create([
            'name'         => 'Flyer A5 Art Paper 150gsm (1 Rim / 500 lembar)',
            'slug'         => 'flyer-a5-ap150-rim',
            'category'     => 'flyer',
            'unit_label'   => '1 Rim (500 lembar)',
            'base_price'   => 150000, // harga cetak 1 sisi per rim
            'thumbnail_url'=> 'https://picsum.photos/seed/flyer-thumb/600/450',
            'images_json'  => [
                'https://picsum.photos/seed/flyer-1/1200/900',
                'https://picsum.photos/seed/flyer-2/1200/900',
            ],
            'spec_json'    => [
                'size'           => 'A5',
                'paper'          => 'art_paper_150',
                'pcs_per_unit'   => 500,
            ],
            'is_active'    => true,
        ]);

        // addon: cetak 2 sisi -> biaya tambahan per rim
        BizAddonPricing::create([
            'biz_product_id'   => $flyer->id,
            'code'             => 'two_side',
            'label'            => 'Cetak 2 sisi',
            'amount_per_unit'  => 30000, // +30rb per rim kalau 2 sisi
            'is_active'        => true,
        ]);


        /*
         |--------------------------------------------------------------------------
         | 2. BROSUR A4 ART PAPER 150GSM (1 Rim / 500 lembar)
         |--------------------------------------------------------------------------
         | Addon:
         | - two_side (Cetak 2 sisi)
         | - fold_half (Lipat 2 / half-fold)
         | - fold_tri (Lipat 3 / tri-fold)
         | - fold_z   (Lipat zig-zag)
         |
         | Tidak ada laminasi.
         */
        $brosur = BizProduct::create([
            'name'         => 'Brosur A4 Art Paper 150gsm (1 Rim / 500 lembar)',
            'slug'         => 'brosur-a4-ap150-rim',
            'category'     => 'brosur',
            'unit_label'   => '1 Rim (500 lembar)',
            'base_price'   => 180000, // harga cetak 1 sisi, tanpa lipat
            'thumbnail_url'=> 'https://picsum.photos/seed/brosur-thumb/600/450',
            'images_json'  => [
                'https://picsum.photos/seed/brosur-1/1200/900',
                'https://picsum.photos/seed/brosur-2/1200/900',
            ],
            'spec_json'    => [
                'flat_size'      => 'A4',
                'paper'          => 'art_paper_150',
                'pcs_per_unit'   => 500,
            ],
            'is_active'    => true,
        ]);

        // addon: cetak 2 sisi
        BizAddonPricing::create([
            'biz_product_id'   => $brosur->id,
            'code'             => 'two_side',
            'label'            => 'Cetak 2 sisi',
            'amount_per_unit'  => 35000, // tambahan per rim
            'is_active'        => true,
        ]);

        // addon: lipat half-fold
        BizAddonPricing::create([
            'biz_product_id'   => $brosur->id,
            'code'             => 'fold_half',
            'label'            => 'Lipat 2 (Half Fold)',
            'amount_per_unit'  => 20000, // +20rb per rim untuk jasa lipat half
            'is_active'        => true,
        ]);

        // addon: lipat tri-fold
        BizAddonPricing::create([
            'biz_product_id'   => $brosur->id,
            'code'             => 'fold_tri',
            'label'            => 'Lipat 3 (Tri Fold)',
            'amount_per_unit'  => 30000, // +30rb per rim
            'is_active'        => true,
        ]);

        // addon: lipat zig-zag
        BizAddonPricing::create([
            'biz_product_id'   => $brosur->id,
            'code'             => 'fold_z',
            'label'            => 'Lipat Zig-Zag',
            'amount_per_unit'  => 32000, // +32rb per rim
            'is_active'        => true,
        ]);


        /*
         |--------------------------------------------------------------------------
         | 3. KARTU NAMA ART CARTON 310GSM (1 Pack / 100 kartu)
         |--------------------------------------------------------------------------
         | Addon:
         | - two_side (Cetak 2 sisi)
         | - laminasi_doff
         | - laminasi_glossy
         |
         | Catatan: tidak semua bahan boleh laminasi, tapi di contoh ini boleh.
         */
        $kartu = BizProduct::create([
            'name'         => 'Kartu Nama Art Carton 310gsm (1 Pack / 100 kartu)',
            'slug'         => 'kartu-nama-ac310-pack',
            'category'     => 'kartu_nama',
            'unit_label'   => '1 Pack (100 kartu)',
            'base_price'   => 80000, // harga 1 sisi, tanpa laminasi, per pack
            'thumbnail_url'=> 'https://picsum.photos/seed/card-thumb/600/450',
            'images_json'  => [
                'https://picsum.photos/seed/card-1/1200/900',
                'https://picsum.photos/seed/card-2/1200/900',
            ],
            'spec_json'    => [
                'paper'         => 'art_carton_310',
                'pcs_per_unit'  => 100,
            ],
            'is_active'    => true,
        ]);

        // addon: cetak 2 sisi
        BizAddonPricing::create([
            'biz_product_id'   => $kartu->id,
            'code'             => 'two_side',
            'label'            => 'Cetak 2 sisi',
            'amount_per_unit'  => 10000, // +10rb per pack
            'is_active'        => true,
        ]);

        // addon: laminasi doff
        BizAddonPricing::create([
            'biz_product_id'   => $kartu->id,
            'code'             => 'lamination_doff',
            'label'            => 'Laminasi Doff',
            'amount_per_unit'  => 8000, // +8rb per pack
            'is_active'        => true,
        ]);

        // addon: laminasi glossy
        BizAddonPricing::create([
            'biz_product_id'   => $kartu->id,
            'code'             => 'lamination_glossy',
            'label'            => 'Laminasi Glossy',
            'amount_per_unit'  => 8000, // +8rb per pack
            'is_active'        => true,
        ]);

        $this->command?->info('✅ BusinessPrintSeeder selesai: flyer, brosur, kartu nama + addons.');
    }
}
