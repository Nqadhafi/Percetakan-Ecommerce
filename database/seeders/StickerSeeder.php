<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Sticker\Models\{
    StickerProduct,
    StickerSpec,
    StickerPriceTier,
    StickerFinishOption
};

class StickerSeeder extends Seeder
{
    public function run(): void
    {
        /** ============================================
         * 1️⃣ PRODUK: Sticker Vinyl A3+
         * ============================================ */
        $vinyl = StickerProduct::create([
            'name' => 'Sticker Vinyl A3+',
            'slug' => 'sticker-vinyl-a3plus',
            'thumbnail_url' => '/images/products/sticker-vinyl-a3plus-thumb.jpg',
            'images_json' => [
                '/images/products/sticker-vinyl-a3plus-1.jpg',
                '/images/products/sticker-vinyl-a3plus-2.jpg',
            ],
            'is_active' => true,
        ]);

        $materialsVinyl = [
            ['material' => 'vinyl_white', 'supports_lamination' => true],
            ['material' => 'vinyl_clear', 'supports_lamination' => true],
            ['material' => 'kraft',       'supports_lamination' => false],
        ];

        foreach ($materialsVinyl as $mat) {
            $spec = StickerSpec::create([
                'product_id' => $vinyl->id,
                'material' => $mat['material'],
                'supports_lamination' => $mat['supports_lamination'],
                'min_qty' => 1,
                'is_active' => true,
            ]);

            foreach ([
                ['min_qty' => 1,   'unit_price' => 12000],
                ['min_qty' => 10,  'unit_price' => 11000],
                ['min_qty' => 50,  'unit_price' => 10000],
                ['min_qty' => 100, 'unit_price' => 9000],
            ] as $tier) {
                StickerPriceTier::create($tier + ['spec_id' => $spec->id]);
            }
        }

        /** ============================================
         * 2️⃣ PRODUK: Sticker Hologram A3+
         * ============================================ */
        $holo = StickerProduct::create([
            'name' => 'Sticker Hologram A3+',
            'slug' => 'sticker-hologram-a3plus',
            'thumbnail_url' => '/images/products/sticker-hologram-a3plus-thumb.jpg',
            'images_json' => [
                '/images/products/sticker-hologram-a3plus-1.jpg',
                '/images/products/sticker-hologram-a3plus-2.jpg',
            ],
            'is_active' => true,
        ]);

        // hanya 1 material hologram (tanpa laminasi)
        $specHolo = StickerSpec::create([
            'product_id' => $holo->id,
            'material' => 'hologram',
            'supports_lamination' => false,
            'min_qty' => 1,
            'is_active' => true,
        ]);

        foreach ([
            ['min_qty' => 1,   'unit_price' => 18000],
            ['min_qty' => 10,  'unit_price' => 17000],
            ['min_qty' => 50,  'unit_price' => 16000],
            ['min_qty' => 100, 'unit_price' => 15000],
        ] as $tier) {
            StickerPriceTier::create($tier + ['spec_id' => $specHolo->id]);
        }

        /** ============================================
         * 3️⃣ FINISHING & LAMINASI (GLOBAL)
         * ============================================ */
        if (StickerFinishOption::count() === 0) {
            $options = [
                // Laminasi (global)
                ['kind'=>'lamination','code'=>'doff','label'=>'Laminasi Doff','pricing_mode'=>'per_sheet','amount'=>500],
                ['kind'=>'lamination','code'=>'glossy','label'=>'Laminasi Glossy','pricing_mode'=>'per_sheet','amount'=>500],

                // Finishing (global)
                ['kind'=>'finish','code'=>'straight_cut','label'=>'Potong Lurus','pricing_mode'=>'flat_job','amount'=>2000],
                ['kind'=>'finish','code'=>'kiss_cut','label'=>'Kiss-cut','pricing_mode'=>'per_sheet','amount'=>300],
                ['kind'=>'finish','code'=>'die_cut','label'=>'Die-cut','pricing_mode'=>'per_sheet','amount'=>600],
            ];
            foreach ($options as $opt) {
                StickerFinishOption::create($opt + ['is_active'=>true]);
            }
        }

        $this->command->info('✅ Sticker Vinyl A3+ dan Sticker Hologram A3+ berhasil disetup.');
    }
}
