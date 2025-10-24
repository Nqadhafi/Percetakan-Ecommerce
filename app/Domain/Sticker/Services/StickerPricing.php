<?php
// app/Domain/Sticker/Services/StickerPricing.php
namespace App\Domain\Sticker\Services;

use App\Domain\Sticker\Models\StickerProduct;
use App\Domain\Sticker\Models\StickerFinishOption;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class StickerPricing
{
    public function quote(array $input): array
    {
        $qty = max(1, (int)($input['qty'] ?? 1));

        $product = StickerProduct::query()
            ->where('slug', $input['product_slug'])
            ->where('is_active', true)
            ->first();

        if (!$product) {
            throw ValidationException::withMessages(['product_slug' => 'Produk tidak ditemukan / nonaktif.']);
        }

        // Cache specs + tiers
        $specs = Cache::remember("sticker_specs:{$product->id}", 1800, function() use ($product) {
            return $product->specs()->where('is_active', true)->with('priceTiers')->get();
        });

        $spec = $specs->first(fn ($s) => $s->material === $input['material']);
        if (!$spec) throw ValidationException::withMessages(['material' => 'Material tidak tersedia.']);

        // laminasi support check
        if (!$spec->supports_lamination && $input['lamination'] !== 'none') {
            throw ValidationException::withMessages(['lamination' => 'Material ini tidak mendukung laminasi.']);
        }

        // Pilih tier grosir (fallback ke tier terkecil)
        $tiers = $spec->priceTiers->sortBy('min_qty')->values();
        $tier = $tiers->filter(fn($t) => $t->min_qty <= $qty)->sortByDesc('min_qty')->first()
             ?? $tiers->first();
        if (!$tier) throw ValidationException::withMessages(['pricing' => 'Tier harga belum diset.']);

        $unit = (int) $tier->unit_price;
        $base = $unit * $qty;

        // ===== Add-on Fees (lamination & finish) =====
        $laminationFee = $this->resolveAddonFee($product->id, $spec->material, 'lamination', $input['lamination'], $qty);
        $finishFee     = $this->resolveAddonFee($product->id, $spec->material, 'finish',     $input['finish'],     $qty);

        $total = $base + $laminationFee + $finishFee;

        return [
            'unit_price' => $unit,
            'qty'        => $qty,
            'total'      => $total,
            'breakdown'  => [
                'base'           => $base,
                'lamination_fee' => $laminationFee,
                'finish_fee'     => $finishFee,
            ],
            'spec_snapshot' => [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'size'         => 'A3+', // atau A3 jika produk spesifik
                'material'     => $spec->material,
                'lamination'   => $input['lamination'],
                'finish'       => $input['finish'],
            ],
        ];
    }

    private function resolveAddonFee(?int $productId, ?string $material, string $kind, string $code, int $qty): int
    {
        if ($code === 'none') return 0;

        // Priority: match by (product+material) → (product-only) → (global)
        $cacheKey = "sticker_addon:$productId:$material:$kind:$code";
        $opt = Cache::remember($cacheKey, 1800, function() use ($productId,$material,$kind,$code) {
            return StickerFinishOption::query()
                ->where('kind', $kind)
                ->where('code', $code)
                ->where('is_active', true)
                ->when($material, fn($q) => $q->where('material', $material))
                ->when($productId, fn($q) => $q->orderByRaw("CASE WHEN product_id = ? THEN 0 ELSE 1 END", [$productId]))
                ->orderByRaw("CASE WHEN product_id IS NULL THEN 1 ELSE 0 END")
                ->first()
            ?? StickerFinishOption::query()
                ->where('kind', $kind)->where('code', $code)
                ->whereNull('material')->whereNull('product_id')->where('is_active', true)->first();
        });

        if (!$opt) throw ValidationException::withMessages([$kind => "Opsi {$kind} '{$code}' belum tersedia."]);

        return $opt->pricing_mode === 'per_sheet' ? (int)$opt->amount * $qty : (int)$opt->amount;
    }
}
