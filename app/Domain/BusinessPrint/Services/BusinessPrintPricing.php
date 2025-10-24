<?php

namespace App\Domain\BusinessPrint\Services;

use App\Domain\BusinessPrint\Models\BizProduct;
use Illuminate\Validation\ValidationException;

class BusinessPrintPricing
{
    public function quote(array $input): array
    {
        // required input:
        // - product_slug
        // - qty_unit (int >=1)
        // - side ('1s'|'2s')
        // - fold_type ('none'|'half_fold'|'tri_fold'|'z_fold') => only for brosur
        // - lamination ('none'|'doff'|'glossy')                => only for kartu_nama

        $qty = max(1, (int)($input['qty_unit'] ?? 1));
        $side = $input['side'] ?? '1s';
        $foldType = $input['fold_type'] ?? 'none';
        $lamination = $input['lamination'] ?? 'none';

        // 1. Ambil produk
        /** @var BizProduct|null $product */
        $product = BizProduct::query()
            ->where('slug', $input['product_slug'] ?? '')
            ->where('is_active', true)
            ->with(['addons' => function ($q) {
                $q->where('is_active', true);
            }])
            ->first();

        if (!$product) {
            throw ValidationException::withMessages([
                'product_slug' => 'Produk tidak ditemukan / tidak aktif.',
            ]);
        }

        $category = $product->category;

        // 2. Konstruksi kamus addon_by_code agar gampang lookup
        $addonByCode = [];
        foreach ($product->addons as $addon) {
            $addonByCode[$addon->code] = $addon; // code => BizAddonPricing
        }

        // 3. base price (per unit paket)
        $basePerUnit = (int) $product->base_price;

        // 4. hitung addon per unit
        $twoSideFeePerUnit = 0;
        if ($side === '2s' && isset($addonByCode['two_side'])) {
            $twoSideFeePerUnit = (int) $addonByCode['two_side']->amount_per_unit;
        }

        $foldFeePerUnit = 0;
        if ($category === 'brosur') {
            if ($foldType === 'half_fold' && isset($addonByCode['fold_half'])) {
                $foldFeePerUnit = (int) $addonByCode['fold_half']->amount_per_unit;
            } elseif ($foldType === 'tri_fold' && isset($addonByCode['fold_tri'])) {
                $foldFeePerUnit = (int) $addonByCode['fold_tri']->amount_per_unit;
            } elseif ($foldType === 'z_fold' && isset($addonByCode['fold_z'])) {
                $foldFeePerUnit = (int) $addonByCode['fold_z']->amount_per_unit;
            } elseif ($foldType !== 'none') {
                // user minta lipatan tapi ga ada harganya
                throw ValidationException::withMessages([
                    'fold_type' => 'Tipe lipatan ini belum tersedia untuk produk ini.',
                ]);
            }
        } else {
            // jika bukan brosur, abaikan fold_type apapun dari FE
            $foldType = 'none';
        }

        $lamFeePerUnit = 0;
        if ($category === 'kartu_nama') {
            if ($lamination === 'doff' && isset($addonByCode['lamination_doff'])) {
                $lamFeePerUnit = (int) $addonByCode['lamination_doff']->amount_per_unit;
            } elseif ($lamination === 'glossy' && isset($addonByCode['lamination_glossy'])) {
                $lamFeePerUnit = (int) $addonByCode['lamination_glossy']->amount_per_unit;
            } elseif ($lamination !== 'none') {
                throw ValidationException::withMessages([
                    'lamination' => 'Laminasi ini belum tersedia untuk bahan kartu ini.',
                ]);
            }
        } else {
            // selain kartu_nama, abaikan lamination dari FE
            $lamination = 'none';
        }

        // 5. final unit price (harga per 1 unit paket setelah addon)
        $unitPriceFinal = $basePerUnit
            + $twoSideFeePerUnit
            + $foldFeePerUnit
            + $lamFeePerUnit;

        // 6. total
        $baseTotal      = $basePerUnit       * $qty;
        $twoSideTotal   = $twoSideFeePerUnit * $qty;
        $foldTotal      = $foldFeePerUnit    * $qty;
        $lamTotal       = $lamFeePerUnit     * $qty;
        $grandTotal     = $unitPriceFinal    * $qty;

        // 7. build snapshot for cart/order_items
        $spec = $product->spec_json ?? [];
        $snapshot = [
            'product_id'     => $product->id,
            'product_name'   => $product->name,
            'category'       => $category,
            'unit_label'     => $product->unit_label,
            // bawain spesifikasi dasar produk
            ...$spec,
            // addon pilihan user yang ngaruh ke produksi
            'side'           => $side,
            'fold_type'      => $foldType,
            'lamination'     => $lamination,
        ];

        return [
            'ok'         => true,
            'unit_price' => $unitPriceFinal, // per 1 rim/pack setelah addon
            'qty'        => $qty,            // jumlah rim/pack yang dipesan
            'total'      => $grandTotal,
            'breakdown'  => [
                'base'            => $baseTotal,
                'two_side_fee'    => $twoSideTotal,
                'fold_fee'        => $foldTotal,
                'lamination_fee'  => $lamTotal,
            ],
            'spec_snapshot' => $snapshot,
        ];
    }
}
