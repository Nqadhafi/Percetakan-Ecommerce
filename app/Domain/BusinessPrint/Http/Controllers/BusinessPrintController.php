<?php

namespace App\Domain\BusinessPrint\Http\Controllers;

use App\Domain\BusinessPrint\Models\BizProduct;
use App\Domain\BusinessPrint\Http\Requests\BizQuoteRequest;
use App\Domain\BusinessPrint\Services\BusinessPrintPricing;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class BusinessPrintController extends Controller
{
    public function index()
    {
        $products = BizProduct::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'category',
                'unit_label',
                'base_price',
                'thumbnail_url',
                'images_json',
            ]);

        return Inertia::render('Catalog/Biz/Index', [
            'products' => $products,
        ]);
    }

    public function show(BizProduct $product)
    {
        abort_unless($product->is_active, 404);

        // addon aktif untuk produk ini
        $addons = $product->addons()
            ->get(['code','label','amount_per_unit']);

        // tentukan opsi apa yang harus ditampilkan di FE
        // default
        $sideOptions = ['1s','2s'];

        $foldOptions = [];
        $laminationOptions = [];

        if ($product->category === 'brosur') {
            // fold_type: hanya munculkan yg ada di addons
            $foldMap = [
                'fold_half' => 'half_fold',
                'fold_tri'  => 'tri_fold',
                'fold_z'    => 'z_fold',
            ];
            $foldOptions = ['none']; // always bisa "tanpa lipat"
            foreach ($addons as $a) {
                if (isset($foldMap[$a->code])) {
                    $foldOptions[] = $foldMap[$a->code];
                }
            }
            $foldOptions = array_values(array_unique($foldOptions));
        }

        if ($product->category === 'kartu_nama') {
            // laminasi: hanya munculkan yg ada di addons
            $lamMap = [
                'lamination_doff'   => 'doff',
                'lamination_glossy' => 'glossy',
            ];
            $laminationOptions = ['none'];
            foreach ($addons as $a) {
                if (isset($lamMap[$a->code])) {
                    $laminationOptions[] = $lamMap[$a->code];
                }
            }
            $laminationOptions = array_values(array_unique($laminationOptions));
        }

        return Inertia::render('Catalog/Biz/Show', [
            'product' => [
                'id'           => $product->id,
                'name'         => $product->name,
                'slug'         => $product->slug,
                'category'     => $product->category,
                'unit_label'   => $product->unit_label,
                'base_price'   => $product->base_price,
                'thumbnail_url'=> $product->thumbnail_url,
                'images_json'  => $product->images_json,
                'spec'         => $product->spec_json,
            ],
            'options' => [
                'side'        => $sideOptions,          // selalu ada
                'fold_type'   => $foldOptions,          // hanya brosur
                'lamination'  => $laminationOptions,    // hanya kartu_nama
                'min_qty'     => 1,
            ],
        ]);
    }

    public function quote(BizQuoteRequest $request, BusinessPrintPricing $pricing)
    {
        $result = $pricing->quote($request->validated());

        // samakan struktur dengan produk lain (ok, unit_price, qty, dsb)
        return response()->json($result);
    }
}
