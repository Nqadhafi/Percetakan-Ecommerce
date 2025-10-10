<?php

namespace App\Domain\MMT\Http\Controllers;

use App\Domain\MMT\Models\MmtProduct;
use App\Domain\MMT\Services\MmtPricing;
use App\Domain\MMT\Services\MmtPackPricing;
use App\Domain\MMT\Http\Requests\MmtQuoteRequest;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MMTController extends Controller
{
    public function index()
    {
        $products = MmtProduct::where('is_active', true)
            ->select('id','name','slug','kind')->orderBy('name')->get();

        return Inertia::render('Catalog/MMT/Index', ['products' => $products]);
    }

    public function show(MmtProduct $product)
    {
        abort_unless($product->is_active, 404);

        if ($product->kind === 'meteran') {
            $data = Cache::remember("mmt_show:{$product->id}", 1800, function() use ($product) {
                return [
                    'materials' => $product->materials()->where('is_active', true)->orderBy('material')->get(['material','base_price_per_m2']),
                    'finishings'=> $product->finishing()->where('is_active', true)->orderBy('finishing')->get(['finishing','price_type','price_value']),
                    'min_area'  => (float)$product->min_area,
                    'width_extra_m' => (float)$product->width_extra_m,
                ];
            });

            return Inertia::render('Catalog/MMT/Show', [
                'product'   => $product->only('id','name','slug','kind'),
                'materials' => $data['materials'],
                'finishings'=> $data['finishings'],
                'min_area'  => $data['min_area'],
                'width_extra_m' => $data['width_extra_m'],
            ]);
        }

        // kind === 'package'
        $materials = $product->packages()->select('material')->distinct()->pluck('material');
        $sizesByMat = [];
        foreach ($materials as $m) {
            $sizesByMat[$m] = $product->packages()->where('material',$m)->pluck('size');
        }

        return Inertia::render('Catalog/MMT/Show', [
            'product'   => $product->only('id','name','slug','kind','has_pole','pole_price','pole_default_include'),
            'materials' => $materials,
            'sizesByMaterial' => $sizesByMat,
        ]);
    }

public function quote(MmtQuoteRequest $r, MmtPricing $meterSvc, MmtPackPricing $packSvc)
{
    $product = MmtProduct::where('slug', $r->input('product_slug'))->firstOrFail();

    if ($product->kind === 'package') {
        $res = $packSvc->quote($r->validated());
    } else {
        $res = $meterSvc->quote($r->validated());
    }

    return response()->json(['ok'=>true] + $res);
}

}
