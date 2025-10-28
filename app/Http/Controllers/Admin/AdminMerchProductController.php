<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Merch\Models\MerchProduct;
use App\Domain\Merch\Models\MerchVariant;
use App\Domain\Merch\Models\MerchPriceTier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class AdminMerchProductController extends Controller
{
    /**
     * List semua produk merch (Tumblr, Mug, Pin, dsb)
     */
    public function index()
    {
        $products = MerchProduct::query()
            ->orderBy('name')
            ->withCount(['variants'])
            ->get([
                'id',
                'name',
                'slug',
                'is_active',
                'min_order_qty',
                'variant_label',
            ]);

        return Inertia::render('Admin/Merch/Index', [
            'products' => $products->map(function($p){
                return [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'slug'          => $p->slug,
                    'is_active'     => $p->is_active,
                    'min_order_qty' => $p->min_order_qty,
                    'variant_label' => $p->variant_label,
                    'variants_count'=> $p->variants_count,
                ];
            }),
        ]);
    }

    public function create()
{
    // Halaman form kosong untuk buat produk baru
    return Inertia::render('Admin/Merch/Create', [
        'defaults' => [
            'name'          => '',
            'slug'          => '',
            'is_active'     => true,
            'min_order_qty' => 1,
            'variant_label' => '',
            'thumbnail_url' => '',
            'images_json'   => [],
        ],
    ]);
}

public function store(Request $request)
{
    $data = $request->validate([
        'name'          => ['required','string','max:200'],
        'slug'          => ['required','string','max:200'],
        'is_active'     => ['required','boolean'],
        'min_order_qty' => ['required','integer','min:1'],
        'variant_label' => ['nullable','string','max:100'],
        'thumbnail_url' => ['nullable','string','max:500'],
        'images_json'   => ['nullable','array'],
    ]);

    $product = MerchProduct::create($data);

    return redirect()
        ->route('admin.merch.edit', $product->id)
        ->with('success', 'Produk baru berhasil dibuat. Sekarang tambahkan varian & tier harga.');
}


    /**
     * Halaman edit 1 produk merch
     * Termasuk semua variants + price tiers
     */
    public function edit(int $id)
    {
        $product = MerchProduct::query()
            ->with(['variants' => function($q){
                $q->with(['tiers' => fn($t) => $t->orderBy('min_qty','asc')])
                  ->orderBy('id','asc');
            }])
            ->findOrFail($id);

        return Inertia::render('Admin/Merch/Edit', [
            'product' => [
                'id'            => $product->id,
                'name'          => $product->name,
                'slug'          => $product->slug,
                'is_active'     => $product->is_active,
                'min_order_qty' => $product->min_order_qty,
                'variant_label' => $product->variant_label,
                'thumbnail_url' => $product->thumbnail_url,
                'images_json'   => $product->images_json,
            ],
            'variants' => $product->variants->map(function($v){
                return [
                    'id'        => $v->id,
                    'code'      => $v->code,
                    'label'     => $v->label,
                    'is_active' => $v->is_active,
                    'tiers'     => $v->tiers->map(function($t){
                        return [
                            'id'         => $t->id,
                            'min_qty'    => $t->min_qty,
                            'unit_price' => $t->unit_price,
                        ];
                    })->values(),
                ];
            })->values(),
        ]);
    }

    /**
     * Update field di merch_products
     */
    public function updateProduct(Request $request, int $id)
    {
        $product = MerchProduct::findOrFail($id);

        $data = $request->validate([
            'name'          => ['required','string','max:200'],
            'slug'          => ['required','string','max:200'],
            'is_active'     => ['required','boolean'],
            'min_order_qty' => ['required','integer','min:1'],
            'variant_label' => ['nullable','string','max:100'],
            'thumbnail_url' => ['nullable','string','max:500'],
            'images_json'   => ['nullable','array'],
        ]);

        $product->update($data);

        return redirect()
            ->route('admin.merch.edit', $product->id)
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Tambah varian baru untuk product tertentu
     * misal: code=box_custom, label="Box Custom"
     */
    public function storeVariant(Request $request, int $id)
    {
        $product = MerchProduct::findOrFail($id);

        $data = $request->validate([
            'code'      => ['required','string','max:50'],
            'label'     => ['required','string','max:100'],
            'is_active' => ['required','boolean'],
        ]);

        // Pastikan code unik per product
        $exists = MerchVariant::where('merch_product_id',$product->id)
            ->where('code',$data['code'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'code' => 'Kode varian sudah dipakai pada produk ini.',
            ]);
        }

        MerchVariant::create([
            'merch_product_id' => $product->id,
            'code'             => $data['code'],
            'label'            => $data['label'],
            'is_active'        => $data['is_active'],
        ]);

        return redirect()
            ->route('admin.merch.edit', $product->id)
            ->with('success', 'Varian ditambahkan.');
    }

    /**
     * Update varian: ubah code/label/is_active
     */
    public function updateVariant(Request $request, int $variantId)
    {
        $variant = MerchVariant::with('product')->findOrFail($variantId);

        $data = $request->validate([
            'code'      => ['required','string','max:50'],
            'label'     => ['required','string','max:100'],
            'is_active' => ['required','boolean'],
        ]);

        // Cek code unik dalam product yang sama (kecuali varian ini)
        $exists = MerchVariant::where('merch_product_id',$variant->merch_product_id)
            ->where('code',$data['code'])
            ->where('id','!=',$variant->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'code' => 'Kode varian sudah dipakai pada produk ini.',
            ]);
        }

        $variant->update($data);

        return redirect()
            ->route('admin.merch.edit', $variant->merch_product_id)
            ->with('success', 'Varian diperbarui.');
    }

    /**
     * Tambah tier harga baru
     */
    public function storeTier(Request $request, int $variantId)
    {
        $variant = MerchVariant::with('product')->findOrFail($variantId);

        $data = $request->validate([
            'min_qty'    => ['required','integer','min:1'],
            'unit_price' => ['required','integer','min:0'],
        ]);

        MerchPriceTier::create([
            'variant_id' => $variant->id,
            'min_qty'    => $data['min_qty'],
            'unit_price' => $data['unit_price'],
        ]);

        return redirect()
            ->route('admin.merch.edit', $variant->merch_product_id)
            ->with('success', 'Tier harga ditambahkan.');
    }

    /**
     * Update tier harga
     */
    public function updateTier(Request $request, int $tierId)
    {
        $tier = MerchPriceTier::with('variant')->findOrFail($tierId);

        $data = $request->validate([
            'min_qty'    => ['required','integer','min:1'],
            'unit_price' => ['required','integer','min:0'],
        ]);

        $tier->update($data);

        return redirect()
            ->route('admin.merch.edit', $tier->variant->merch_product_id)
            ->with('success', 'Tier harga diperbarui.');
    }
}
