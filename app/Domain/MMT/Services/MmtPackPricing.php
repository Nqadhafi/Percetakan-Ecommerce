<?php

namespace App\Domain\MMT\Services;

use App\Domain\Contracts\ProductPricingContract;
use App\Domain\MMT\Models\MmtProduct;
use Illuminate\Validation\ValidationException;

class MmtPackPricing implements ProductPricingContract
{
    public function quote(array $input): array
    {
        $product = MmtProduct::query()
            ->where('slug', $input['product_slug'])
            ->where('is_active', true)->first();

        if (!$product || $product->kind !== 'package') {
            throw ValidationException::withMessages(['product_slug' => 'Produk paket tidak ditemukan / nonaktif.']);
        }

        $material = (string)($input['material'] ?? '');
        $size     = (string)($input['size'] ?? '');
        $qty      = max(1, (int)($input['qty'] ?? 1));
        $includePole = (bool)($input['include_pole'] ?? $product->pole_default_include);

        $row = $product->packages()->where('material', $material)->where('size', $size)->first();
        if (!$row) throw ValidationException::withMessages(['variant' => 'Kombinasi material & size tidak tersedia.']);

        // Aturan: default_include = true => base_price termasuk tiang
        $baseUnit = (float)$row->base_price;
        $unit = $baseUnit;

        if ($product->has_pole) {
            $pole = (float)$product->pole_price;
            if ($product->pole_default_include) {
                // sudah termasuk; jika user tidak mau tiang → kurangi
                if (!$includePole) $unit = max(0, $baseUnit - $pole);
            } else {
                // default tanpa tiang; jika user ingin tiang → tambah
                if ($includePole) $unit = $baseUnit + $pole;
            }
        }

        $total = $unit * $qty;

        return [
            'unit_price' => round($unit, 2),
            'qty'        => $qty,
            'total'      => round($total, 2),
            'breakdown'  => [
                'base_unit'  => round($baseUnit, 2),
                'include_pole' => $includePole,
                'pole_price' => (float)$product->pole_price,
                'material'   => $material,
                'size'       => $size,
            ],
            'spec_snapshot' => [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'material'     => $material,
                'size'         => $size,
                'include_pole' => $includePole,
            ],
        ];
    }
}
