<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\BusinessPrint\Models\BizProduct;
use App\Domain\BusinessPrint\Models\BizAddonPricing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AdminBizProductController extends Controller
{
    /**
     * List semua produk business print
     * contoh kategori: brosur, kartu_nama, dsb
     */
    public function index()
    {
        $products = BizProduct::query()
            ->orderBy('category')
            ->orderBy('name')
            ->withCount(['addons'])
            ->get([
                'id',
                'name',
                'slug',
                'category',
                'unit_label',
                'base_price',
                'is_active',
            ]);

        return Inertia::render('Admin/Biz/Index', [
            'products' => $products->map(function ($p) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'slug'        => $p->slug,
                    'category'    => $p->category,
                    'unit_label'  => $p->unit_label,
                    'base_price'  => $p->base_price,
                    'is_active'   => $p->is_active,
                    'addons_count'=> $p->addons_count,
                ];
            })->values(),
        ]);
    }

    /**
     * Halaman form kosong untuk buat produk baru
     */
    public function create()
    {
        return Inertia::render('Admin/Biz/Create', [
            'defaults' => [
                'name'          => '',
                'slug'          => '',
                'category'      => '',
                'unit_label'    => '',
                'base_price'    => 0,
                'thumbnail_url' => '',
                'images_json'   => [],
                'spec_json'     => [],
                'is_active'     => true,
            ],
        ]);
    }

    /**
     * Simpan produk baru
     */
public function store(Request $request)
{
    $data = $request->validate([
        'name'          => ['required','string','max:150'],
        'slug'          => ['required','string','max:150','unique:biz_products,slug'],
        'category'      => ['required','string','max:50'],
        'unit_label'    => ['required','string','max:100'],
        'base_price'    => ['required','integer','min:0'],
        'thumbnail_url' => ['nullable','string','max:500'],
        'images_json'   => ['nullable','array'],
        'spec_json'     => ['nullable','array'],
        'is_active'     => ['required','boolean'],

        // ini tambahan penting:
        'preset_addons'                        => ['nullable','array'],
        'preset_addons.*.code'                 => ['required','string','max:100'],
        'preset_addons.*.label'                => ['required','string','max:150'],
        'preset_addons.*.amount_per_unit'      => ['required','integer','min:0'],
        'preset_addons.*.is_active'            => ['required','boolean'],
    ]);

    $product = BizProduct::create([
        'name'          => $data['name'],
        'slug'          => $data['slug'],
        'category'      => $data['category'],
        'unit_label'    => $data['unit_label'],
        'base_price'    => $data['base_price'],
        'thumbnail_url' => $data['thumbnail_url'] ?? null,
        'images_json'   => $data['images_json'] ?? [],
        'spec_json'     => $data['spec_json'] ?? [],
        'is_active'     => $data['is_active'],
    ]);

    // ← BAGIAN BARU INI
    if (!empty($data['preset_addons'])) {
        foreach ($data['preset_addons'] as $row) {
            BizAddonPricing::create([
                'biz_product_id'   => $product->id,
                'code'             => $row['code'],
                'label'            => $row['label'],
                'amount_per_unit'  => $row['amount_per_unit'],
                'is_active'        => $row['is_active'],
            ]);
        }
    }

    return redirect()
        ->route('admin.biz.edit', $product->id)
        ->with('success', 'Produk baru dibuat. Addon default sudah ditambahkan.');
}


    /**
     * Halaman edit 1 produk business print
     * Sekalian load semua addon pricings
     */
    public function edit(int $id)
    {
        $product = BizProduct::query()
            ->with(['addons' => function ($q) {
                $q->orderBy('code');
            }])
            ->findOrFail($id);

        return Inertia::render('Admin/Biz/Edit', [
            'product' => [
                'id'            => $product->id,
                'name'          => $product->name,
                'slug'          => $product->slug,
                'category'      => $product->category,
                'unit_label'    => $product->unit_label,
                'base_price'    => $product->base_price,
                'thumbnail_url' => $product->thumbnail_url,
                'images_json'   => $product->images_json ?? [],
                'spec_json'     => $product->spec_json ?? [],
                'is_active'     => $product->is_active,
            ],
            'addons' => $product->addons->map(function ($a) {
                return [
                    'id'               => $a->id,
                    'code'             => $a->code,
                    'label'            => $a->label,
                    'amount_per_unit'  => $a->amount_per_unit,
                    'is_active'        => $a->is_active,
                ];
            })->values(),

            // small help text buat admin biar ga bingung isi "code"
            'hint_codes' => [
                'two_side'           => 'Biaya tambahan cetak 2 sisi',
                'fold_half'          => 'Biaya lipat 2 (brosur)',
                'fold_tri'           => 'Biaya lipat 3 (brosur)',
                'fold_z'             => 'Biaya lipat zig-zag (brosur)',
                'lamination_doff'    => 'Laminasi doff (kartu nama)',
                'lamination_glossy'  => 'Laminasi glossy (kartu nama)',
            ],
        ]);
    }

    /**
     * Update field di biz_products
     */
    public function updateProduct(Request $request, int $id)
    {
        $product = BizProduct::findOrFail($id);

        $data = $request->validate([
            'name'          => ['required','string','max:150'],
            'slug'          => [
                'required','string','max:150',
                Rule::unique('biz_products','slug')->ignore($product->id),
            ],
            'category'      => ['required','string','max:50'],
            'unit_label'    => ['required','string','max:100'],
            'base_price'    => ['required','integer','min:0'],
            'thumbnail_url' => ['nullable','string','max:500'],
            'images_json'   => ['nullable','array'],
            'spec_json'     => ['nullable','array'],
            'is_active'     => ['required','boolean'],
        ]);

        $product->update([
            'name'          => $data['name'],
            'slug'          => $data['slug'],
            'category'      => $data['category'],
            'unit_label'    => $data['unit_label'],
            'base_price'    => $data['base_price'],
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'images_json'   => $data['images_json'] ?? [],
            'spec_json'     => $data['spec_json'] ?? [],
            'is_active'     => $data['is_active'],
        ]);

        return redirect()
            ->route('admin.biz.edit', $product->id)
            ->with('success', 'Produk diperbarui.');
    }

    /**
     * Tambah addon baru (example: two_side, fold_half, lamination_doff)
     */
    public function storeAddon(Request $request, int $id)
    {
        $product = BizProduct::findOrFail($id);

        $data = $request->validate([
            'code'             => ['required','string','max:100'],
            'label'            => ['required','string','max:150'],
            'amount_per_unit'  => ['required','integer','min:0'],
            'is_active'        => ['required','boolean'],
        ]);

        // Pastikan tidak duplikat code dalam satu produk
        $exists = BizAddonPricing::where('biz_product_id', $product->id)
            ->where('code', $data['code'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'code' => 'Kode addon sudah ada untuk produk ini.',
            ]);
        }

        BizAddonPricing::create([
            'biz_product_id'   => $product->id,
            'code'             => $data['code'],
            'label'            => $data['label'],
            'amount_per_unit'  => $data['amount_per_unit'],
            'is_active'        => $data['is_active'],
        ]);

        return redirect()
            ->route('admin.biz.edit', $product->id)
            ->with('success', 'Addon ditambahkan.');
    }

    /**
     * Update addon existing
     */
    public function updateAddon(Request $request, int $addonId)
    {
        $addon = BizAddonPricing::with('product')->findOrFail($addonId);

        $data = $request->validate([
            'code'             => ['required','string','max:100'],
            'label'            => ['required','string','max:150'],
            'amount_per_unit'  => ['required','integer','min:0'],
            'is_active'        => ['required','boolean'],
        ]);

        // Pastikan code unik per product (kecuali addon ini sendiri)
        $exists = BizAddonPricing::where('biz_product_id', $addon->biz_product_id)
            ->where('code', $data['code'])
            ->where('id', '!=', $addon->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'code' => 'Kode addon sudah ada untuk produk ini.',
            ]);
        }

        $addon->update([
            'code'             => $data['code'],
            'label'            => $data['label'],
            'amount_per_unit'  => $data['amount_per_unit'],
            'is_active'        => $data['is_active'],
        ]);

        return redirect()
            ->route('admin.biz.edit', $addon->biz_product_id)
            ->with('success', 'Addon diperbarui.');
    }

    /**
     * Nonaktifkan addon (soft delete style)
     */
    public function disableAddon(int $addonId)
    {
        $addon = BizAddonPricing::with('product')->findOrFail($addonId);

        $addon->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.biz.edit', $addon->biz_product_id)
            ->with('success', 'Addon dinonaktifkan.');
    }
}
