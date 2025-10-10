<?php

namespace App\Domain\POP\Http\Controllers;

use App\Domain\POP\Models\PopProduct;
use App\Domain\POP\Http\Requests\PopQuoteRequest;
use App\Domain\POP\Services\PopPricing;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class POPController extends Controller
{
    public function index()
    {
        $products = PopProduct::query()
            ->where('is_active', true)
            ->select('id','name','slug')
            ->orderBy('name')
            ->get();

        return Inertia::render('Catalog/POP/Index', [
            'products' => $products,
        ]);
    }

public function show(PopProduct $product)
{
    abort_unless($product->is_active, 404);

    $specs = Cache::remember("pop_specs_show:{$product->id}", 1800, function() use ($product) {
        return $product->specs()
            ->where('is_active', true)
            ->with(['priceTiers' => fn($q) => $q->orderBy('min_qty')])
            ->get(['id','product_id','size','material','side','lamination','cutting','min_qty','step_qty']);
    });

        $options = [
        'materials'  => $specs->pluck('material')->unique()->values(),
        'sides'      => $specs->pluck('side')->unique()->values(),
        'laminations'=> $specs->pluck('lamination')->unique()->values(),
        'cuttings'   => $specs->pluck('cutting')->unique()->values(),
        'min_qty'    => optional($specs->first())->min_qty ?? 50,
        'step_qty'   => optional($specs->first())->step_qty ?? 50,
        ];

    // ⬇️ kirim daftar kombinasi valid (ringkas, tanpa relations)
    $specCombos = $specs->map(fn($s) => [
        'size'       => $s->size,
        'material'   => $s->material,
        'side'       => $s->side,
        'lamination' => $s->lamination,
        'cutting'    => $s->cutting,
    ])->values();

    return Inertia::render('Catalog/POP/Show', [
        'product' => $product->only('id','name','slug'),
        'options' => $options,
        'specs'   => $specCombos, // ⬅️ penting untuk filter di FE
    ]);
}


    public function quote(PopQuoteRequest $request, PopPricing $service)
    {
        $result = $service->quote($request->validated());
        return response()->json(['ok' => true] + $result);
    }
}
