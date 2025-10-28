<?php

namespace App\Domain\Merch\Services;

use App\Domain\Merch\Models\MerchProduct;
use Illuminate\Validation\ValidationException;

class MerchPricing
{
    public function quote(array $input): array
    {
        // input:
        // - product_slug
        // - variant_code
        // - qty

        $qty = max(1, (int)($input['qty'] ?? 1));

        /** @var MerchProduct|null $product */
        $product = MerchProduct::query()
            ->where('slug', $input['product_slug'] ?? '')
            ->where('is_active', true)
            ->with(['variants' => function($q){
                $q->where('is_active', true)->with(['tiers']);
            }])
            ->first();

        if (!$product) {
            throw ValidationException::withMessages([
                'product_slug' => 'Produk tidak ditemukan / tidak aktif.'
            ]);
        }

        // cek min order
        if ($qty < $product->min_order_qty) {
            throw ValidationException::withMessages([
                'qty' => "Minimal order untuk produk ini adalah {$product->min_order_qty} pcs."
            ]);
        }

        // cari variant
        $variantCode = $input['variant_code'] ?? null;
        $variant = $product->variants
            ->first(fn($v) => $v->code === $variantCode);

        if (!$variant) {
            throw ValidationException::withMessages([
                'variant_code' => 'Varian tidak tersedia.'
            ]);
        }

        // ambil tier harga yang berlaku
        $tier = $variant->tiers
            ->filter(fn($t) => $t->min_qty <= $qty)
            ->sortByDesc('min_qty')
            ->first();

        // fallback kalau somehow gak nemu tier <= qty,
        // ambil tier terkecil aja (harusnya jarang terjadi karena min_qty kita mulai dari min_order_qty)
        if (!$tier) {
            $tier = $variant->tiers->sortBy('min_qty')->first();
        }

        if (!$tier) {
            throw ValidationException::withMessages([
                'pricing' => 'Belum ada tier harga untuk varian ini.'
            ]);
        }

        $unit = (int) $tier->unit_price;
        $baseTotal = $unit * $qty;

        return [
            'ok'         => true,
            'unit_price' => $unit,     // harga per pcs untuk qty ini
            'qty'        => $qty,
            'total'      => $baseTotal,
            'breakdown'  => [
                'base' => $baseTotal,
            ],
            'spec_snapshot' => [
                'product_id'     => $product->id,
                'product_name'   => $product->name,
                'variant_code'   => $variant->code,
                'variant_label'  => $variant->label,
                'min_order_qty'  => $product->min_order_qty,
                // untuk detail invoice/cart UI kita juga kirim misc info
                'variant_field'  => $product->variant_label, // "Packaging" / "Ukuran"
            ],
        ];
    }
}