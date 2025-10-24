<?php
// app/Domain/Sticker/Http/Controllers/StickerController.php
namespace App\Domain\Sticker\Http\Controllers;

use App\Domain\Sticker\Models\StickerProduct;
use App\Domain\Sticker\Http\Requests\StickerQuoteRequest;
use App\Domain\Sticker\Services\StickerPricing;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class StickerController extends Controller
{
    public function index()
    {
        $products = StickerProduct::query()
            ->where('is_active', true)
            ->select('id','name','slug','thumbnail_url','images_json')
            ->orderBy('name')
            ->get();

        return Inertia::render('Catalog/Sticker/Index', [
            'products' => $products,
        ]);
    }

    public function show(StickerProduct $product)
    {
        abort_unless($product->is_active, 404);

        $specs = Cache::remember("sticker_specs_show:{$product->id}", 1800, function() use ($product) {
            return $product->specs()
                ->where('is_active', true)
                ->with(['priceTiers' => fn($q) => $q->orderBy('min_qty')])
                ->get(['id','product_id','material','supports_lamination','min_qty']);
        });

        $options = [
            'materials' => $specs->pluck('material')->unique()->values(),
            'min_qty'   => 1,
            'step_qty'  => 1,
            'laminations' => ['none','doff','glossy'],
            'finishes'    => ['none','straight_cut','kiss_cut','die_cut'],
        ];

        return Inertia::render('Catalog/Sticker/Show', [
            'product' => $product->only('id','name','slug','thumbnail_url','images_json'),
            'options' => $options,
            'specs'   => $specs->map(fn($s)=>[
                'material' => $s->material,
                'supports_lamination' => (bool)$s->supports_lamination,
                'min_qty' => $s->min_qty,
                'tiers'   => $s->priceTiers->map(fn($t)=>['min_qty'=>$t->min_qty,'unit_price'=>$t->unit_price]),
            ])->values(),
        ]);
    }

    public function quote(StickerQuoteRequest $request, StickerPricing $service)
    {
        $result = $service->quote($request->validated());
        return response()->json(['ok'=>true] + $result);
    }
}
