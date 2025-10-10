<?php

namespace App\Domain\MMT\Services;

use App\Domain\Contracts\ProductPricingContract;
use App\Domain\MMT\Models\MmtProduct;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class MmtPricing implements ProductPricingContract
{
    public function quote(array $input): array
    {
        $product = MmtProduct::query()
            ->where('slug', $input['product_slug'])
            ->where('is_active', true)->first();

        if (!$product || $product->kind !== 'meteran') {
            throw ValidationException::withMessages(['product_slug' => 'Produk meteran tidak ditemukan / nonaktif.']);
        }

        // Dimensi → meter
        $w = (float)$input['width'];
        $h = (float)$input['height'];
        if ($input['unit'] === 'cm') { $w /= 100; $h /= 100; }
        if ($w <= 0 || $h <= 0) throw ValidationException::withMessages(['dimensi' => 'Dimensi tidak valid.']);

        $extra = (float)$product->width_extra_m;         // +0.2m misalnya
        $effW  = $w + $extra;
        $area  = max((float)$product->min_area, round($h * $effW, 4));

        // Cache data
        $key = "mmt:{$product->id}:data";
        $data = Cache::remember($key, 1800, function () use ($product) {
            return [
                'materials' => $product->materials()->where('is_active', true)->get(['material','base_price_per_m2']),
                'finishings'=> $product->finishing()->where('is_active', true)->get(['finishing','price_type','price_value']),
            ];
        });

        $mat = collect($data['materials'])->firstWhere('material', $input['material']);
        if (!$mat) throw ValidationException::withMessages(['material' => 'Material tidak tersedia.']);

        $pricePerM2 = (float)$mat->base_price_per_m2;
        $base = $area * $pricePerM2;

        // Finishing
        $perimeter = 2 * ($effW + $h); // ikut extra width
        $finList = collect($input['finishing'] ?? []);
        $finDefs = collect($data['finishings']);

        $finTotal = 0.0; $finBreak = [];
        foreach ($finList as $f) {
            $def = $finDefs->firstWhere('finishing', $f['name'] ?? '');
            if (!$def) continue;

            $type = $def->price_type; $val = (float)$def->price_value; $row = 0.0;
            if ($type === 'perimeter') {
                $row = $perimeter * $val;
            } elseif ($type === 'per_point') {
                $qty = max(1, (int)($f['qty'] ?? 1));
                $row = $qty * $val;
            } else { // flat
                $row = $val;
            }
            $finTotal += $row;
            $finBreak[] = [
                'name' => $def->finishing, 'type' => $type, 'value'=> $val,
                'calc' => $type === 'perimeter' ? "$perimeter m × $val"
                      : ($type === 'per_point' ? (max(1,(int)($f['qty']??1)))." × $val" : "flat $val"),
                'total'=> round($row, 2),
            ];
        }

        $total = $base + $finTotal;

        return [
            'unit_price' => round($pricePerM2, 2), // infomasi harga per m2
            'qty'        => 1,
            'total'      => round($total, 2),
            'breakdown'  => [
                'width_input_m'  => round($w, 3),
                'height_input_m' => round($h, 3),
                'width_extra_m'  => round($extra, 3),
                'effective_width_m' => round($effW, 3),
                'area_m2'        => $area,
                'material_price_per_m2' => $pricePerM2,
                'base'           => round($base, 2),
                'finishing'      => $finBreak,
                'finishing_total'=> round($finTotal, 2),
            ],
            'spec_snapshot' => [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'width_m'      => round($w, 3),
                'height_m'     => round($h, 3),
                'effective_width_m' => round($effW, 3),
                'material'     => $mat->material,
                'area_m2'      => $area,
                'finishing'    => $finList,
            ],
        ];
    }
}
