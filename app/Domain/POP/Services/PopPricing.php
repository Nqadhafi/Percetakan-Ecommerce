<?php

namespace App\Domain\POP\Services;

use App\Domain\Contracts\ProductPricingContract;
use App\Domain\POP\Models\PopProduct;
use App\Domain\POP\Models\PopSpec;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PopPricing implements ProductPricingContract
{
    // Add-on biaya cutting sederhana (opsional, bisa 0)
    private array $cuttingFees = [
        'tanpa_potong'        => 0.00,
        'potong_lurus'        => 50.00,   // contoh biaya per 100 lembar
        'potong_pola_die_cut' => 300.00,  // contoh biaya per 100 lembar
    ];

    public function quote(array $input): array
    {
        // input: product_slug, size, material, side, lamination, cutting, qty
        $qty = max(1, (int)($input['qty'] ?? 1));

        $product = PopProduct::query()
            ->where('slug', $input['product_slug'])
            ->where('is_active', true)
            ->first();

        if (!$product) {
            throw ValidationException::withMessages(['product_slug' => 'Produk tidak ditemukan / nonaktif.']);
        }

        // Cache spesifikasi aktif untuk produk
        $specKey = "pop_specs:{$product->id}";
        $specs = Cache::remember($specKey, 1800, function() use ($product) {
            return $product->specs()
                ->where('is_active', true)
                ->with('priceTiers')
                ->get();
        });

        /** @var PopSpec|null $spec */
        $spec = $specs->first(function($s) use ($input) {
            return
            $s->material  === $input['material']
            && $s->side      === $input['side']
            && $s->lamination=== $input['lamination']
            && $s->cutting   === $input['cutting'];
        });

        if (!$spec) {
            throw ValidationException::withMessages(['spec' => 'Kombinasi spesifikasi tidak tersedia.']);
        }

        // Pastikan qty memenuhi minimal & kelipatan step
        if ($qty < $spec->min_qty || ($qty - $spec->min_qty) % max(1, $spec->step_qty) !== 0) {
            throw ValidationException::withMessages([
                'qty' => "Qty harus ≥ {$spec->min_qty} dan kelipatan {$spec->step_qty}."
            ]);
        }

        // Ambil tier: min_qty <= qty terbesar
        $tier = $spec->priceTiers
            ->filter(fn($t) => $t->min_qty <= $qty)
            ->sortByDesc('min_qty')
            ->first();

        if (!$tier) {
            throw ValidationException::withMessages(['pricing' => 'Tier harga belum diset untuk qty ini.']);
        }

        $unit = (float) $tier->unit_price;

        // Cutting fee (contoh): dihitung per 100 lembar
        $cutting = $input['cutting'];
        $cutFeePer100 = $this->cuttingFees[$cutting] ?? 0.0;
        $cutFee = ($cutFeePer100 / 100.0) * $qty;

        $base = $unit * $qty;
        $total = $base + $cutFee;

        return [
            'unit_price' => round($unit, 2),
            'qty' => $qty,
            'total' => round($total, 2),
            'breakdown' => [
                'base' => round($base, 2),
                'cutting_fee' => round($cutFee, 2),
            ],
            'spec_snapshot' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'size' => $spec->size,
                'material' => $spec->material,
                'side' => $spec->side,
                'lamination' => $spec->lamination,
                'cutting' => $spec->cutting,
            ],
        ];
    }
}
