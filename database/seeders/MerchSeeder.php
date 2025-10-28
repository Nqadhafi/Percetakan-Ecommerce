<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Merch\Models\MerchProduct;
use App\Domain\Merch\Models\MerchVariant;
use App\Domain\Merch\Models\MerchPriceTier;

class MerchSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * 1. Tumblr Kancing + Custom UV
         *    Variasi: box polos, box custom
         *    Min order: 1 pcs
         */
        $tumblr = MerchProduct::create([
            'name'           => 'Tumblr Kancing + Custom UV',
            'slug'           => 'tumblr-kancing-uv',
            'variant_label'  => 'Packaging',
            'min_order_qty'  => 1,
            'thumbnail_url'  => 'https://picsum.photos/seed/tumblr1-thumb/600/450',
            'images_json'    => [
                'https://picsum.photos/seed/tumblr1-a/1200/900',
                'https://picsum.photos/seed/tumblr1-b/1200/900',
            ],
            'is_active'      => true,
        ]);

        $tumblrPolos = MerchVariant::create([
            'merch_product_id' => $tumblr->id,
            'code'             => 'box_polos',
            'label'            => 'Box Polos',
            'is_active'        => true,
        ]);

        $tumblrCustom = MerchVariant::create([
            'merch_product_id' => $tumblr->id,
            'code'             => 'box_custom',
            'label'            => 'Box Custom',
            'is_active'        => true,
        ]);

        // Tier harga per pcs utk tumblr box polos
        foreach ([
            ['min_qty' => 1,  'unit_price' => 55000],
            ['min_qty' => 10, 'unit_price' => 52000],
            ['min_qty' => 50, 'unit_price' => 49000],
        ] as $t) {
            MerchPriceTier::create($t + ['variant_id' => $tumblrPolos->id]);
        }

        // Tier harga per pcs utk tumblr box custom (lebih mahal dikit)
        foreach ([
            ['min_qty' => 1,  'unit_price' => 60000],
            ['min_qty' => 10, 'unit_price' => 57000],
            ['min_qty' => 50, 'unit_price' => 54000],
        ] as $t) {
            MerchPriceTier::create($t + ['variant_id' => $tumblrCustom->id]);
        }

        /*
         * 2. Pin Peniti
         *    Variasi: uk 4.4, uk 5.8
         *    Min order: 5 pcs
         */
        $pinPeniti = MerchProduct::create([
            'name'           => 'Pin Peniti',
            'slug'           => 'pin-peniti',
            'variant_label'  => 'Ukuran',
            'min_order_qty'  => 5,
            'thumbnail_url'  => 'https://picsum.photos/seed/pin-peniti-thumb/600/450',
            'images_json'    => [
                'https://picsum.photos/seed/pin-peniti-a/1200/900',
                'https://picsum.photos/seed/pin-peniti-b/1200/900',
            ],
            'is_active'      => true,
        ]);

        $pin44 = MerchVariant::create([
            'merch_product_id' => $pinPeniti->id,
            'code'             => 'uk_4_4',
            'label'            => 'Ukuran 4.4 cm',
            'is_active'        => true,
        ]);

        $pin58 = MerchVariant::create([
            'merch_product_id' => $pinPeniti->id,
            'code'             => 'uk_5_8',
            'label'            => 'Ukuran 5.8 cm',
            'is_active'        => true,
        ]);

        // Tier harga per pcs utk pin 4.4 cm
        foreach ([
            ['min_qty' => 5,  'unit_price' => 8000],
            ['min_qty' => 20, 'unit_price' => 6500],
            ['min_qty' => 50, 'unit_price' => 5500],
        ] as $t) {
            MerchPriceTier::create($t + ['variant_id' => $pin44->id]);
        }

        // Tier harga per pcs utk pin 5.8 cm (lebih mahal dikit)
        foreach ([
            ['min_qty' => 5,  'unit_price' => 9000],
            ['min_qty' => 20, 'unit_price' => 7500],
            ['min_qty' => 50, 'unit_price' => 6500],
        ] as $t) {
            MerchPriceTier::create($t + ['variant_id' => $pin58->id]);
        }

        /*
         * 3. Mug Custom 1 Sisi
         *    Variasi: include box, tanpa box
         *    Min order: 1 pcs
         */
        $mug = MerchProduct::create([
            'name'           => 'Mug Custom 1 Sisi',
            'slug'           => 'mug-custom-1-sisi',
            'variant_label'  => 'Packaging',
            'min_order_qty'  => 1,
            'thumbnail_url'  => 'https://picsum.photos/seed/mug-thumb/600/450',
            'images_json'    => [
                'https://picsum.photos/seed/mug-a/1200/900',
                'https://picsum.photos/seed/mug-b/1200/900',
            ],
            'is_active'      => true,
        ]);

        $mugNoBox = MerchVariant::create([
            'merch_product_id' => $mug->id,
            'code'             => 'tanpa_box',
            'label'            => 'Tanpa Box',
            'is_active'        => true,
        ]);

        $mugWithBox = MerchVariant::create([
            'merch_product_id' => $mug->id,
            'code'             => 'include_box',
            'label'            => 'Include Box',
            'is_active'        => true,
        ]);

        foreach ([
            // Tanpa box sedikit lebih murah
            ['min_qty' => 1,  'unit_price' => 48000],
            ['min_qty' => 12, 'unit_price' => 45000],
            ['min_qty' => 50, 'unit_price' => 42000],
        ] as $t) {
            MerchPriceTier::create($t + ['variant_id' => $mugNoBox->id]);
        }

        foreach ([
            // Include box sedikit lebih mahal
            ['min_qty' => 1,  'unit_price' => 52000],
            ['min_qty' => 12, 'unit_price' => 49000],
            ['min_qty' => 50, 'unit_price' => 46000],
        ] as $t) {
            MerchPriceTier::create($t + ['variant_id' => $mugWithBox->id]);
        }

        $this->command?->info('✅ MerchSeeder selesai: Tumblr, Pin, Mug beserta variannya');
    }
}
