<?php

namespace App\Domain\Merch\Http\Controllers;

use App\Domain\Merch\Models\MerchProduct;
use App\Domain\Merch\Http\Requests\MerchQuoteRequest;
use App\Domain\Merch\Services\MerchPricing;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class MerchController extends Controller
{
    public function index()
    {
        $products = MerchProduct::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'variant_label',
                'min_order_qty',
                'thumbnail_url',
                'images_json',
            ]);

        return Inertia::render('Catalog/Merch/Index', [
            'products' => $products,
        ]);
    }

    public function show(MerchProduct $product)
    {
        abort_unless($product->is_active, 404);

        $variants = $product->variants()
            ->with(['tiers' => fn($q) => $q->orderBy('min_qty')])
            ->get(['id','code','label']);

        return Inertia::render('Catalog/Merch/Show', [
            'product' => [
                'id'            => $product->id,
                'name'          => $product->name,
                'slug'          => $product->slug,
                'variant_label' => $product->variant_label,   // e.g. "Ukuran", "Packaging"
                'min_order_qty' => $product->min_order_qty,
                'thumbnail_url' => $product->thumbnail_url,
                'images_json'   => $product->images_json,
            ],
            'variants' => $variants->map(fn($v) => [
                'code' => $v->code,
                'label'=> $v->label,
            ]),
        ]);
    }

    public function quote(MerchQuoteRequest $request, MerchPricing $svc)
    {
        $result = $svc->quote($request->validated());
        return response()->json($result);
    }
}
