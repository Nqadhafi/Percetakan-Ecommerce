<script setup>
import { reactive, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

/**
 * Preset produk.
 * - menentukan category (penting buat logic FE customer)
 * - menentukan default unit_label (biar admin gak mikir istilahnya dari nol)
 * - menentukan addon default yang akan langsung ditanam saat create
 *
 * amount_per_unit kita set 0 di awal, admin bisa edit harga addon nanti di halaman Edit.
 */
const PRESETS = {
  kartu_nama: {
    label: 'Kartu Nama',
    category: 'kartu_nama',
    unit_label: 'per box isi 100 pcs',
    addons: [
      { code: 'two_side',           label: 'Cetak 2 Sisi',                amount_per_unit: 0, is_active: 1 },
      { code: 'lamination_doff',    label: 'Laminasi Doff',               amount_per_unit: 0, is_active: 1 },
      { code: 'lamination_glossy',  label: 'Laminasi Glossy',             amount_per_unit: 0, is_active: 1 },
    ],
  },
  brosur: {
    label: 'Brosur / Flyer Lipat',
    category: 'brosur',
    unit_label: 'per rim (100 lembar)',
    addons: [
      { code: 'two_side',  label: 'Cetak 2 Sisi',                         amount_per_unit: 0, is_active: 1 },
      { code: 'fold_half', label: 'Lipat 2 (Half Fold)',                  amount_per_unit: 0, is_active: 1 },
      { code: 'fold_tri',  label: 'Lipat 3 (Tri Fold)',                   amount_per_unit: 0, is_active: 1 },
      { code: 'fold_z',    label: 'Lipat Zig-Zag (Z Fold)',               amount_per_unit: 0, is_active: 1 },
    ],
  },
  custom_generic: {
    label: 'Custom Generic / Lainnya',
    category: 'custom_generic',
    unit_label: 'per paket',
    addons: [
      { code: 'two_side',  label: 'Cetak 2 Sisi',                         amount_per_unit: 0, is_active: 1 },
    ],
  },
}

// props.defaults dari controller create()
// (kita tetap terima defaults supaya form punya nilai awal stabil)
const props = defineProps({
  defaults: {
    type: Object,
    required: true,
    // {
    //   name, slug,
    //   category, unit_label,
    //   base_price,
    //   thumbnail_url,
    //   images_json: [],
    //   spec_json: [],
    //   is_active
    // }
  },
})

/**
 * State form produk baru
 */
const formProduct = reactive({
  // diisi manual admin
  name:          props.defaults.name,
  slug:          props.defaults.slug,

  // ini bisa auto berubah saat admin pilih preset
  category:      props.defaults.category,
  unit_label:    props.defaults.unit_label,
  base_price:    props.defaults.base_price,

  // gambar & spec di tahap create kita lewati dulu,
  // akan dikelola lebih lanjut di halaman Edit nanti
  thumbnail_url: props.defaults.thumbnail_url,
  images_json:   props.defaults.images_json,
  spec_json:     props.defaults.spec_json,

  // status aktif/nonaktif
  is_active:     props.defaults.is_active ? 1 : 0,
})

/**
 * Admin pilih tipe produk → kita apply preset
 */
const selectedPresetKey = ref('kartu_nama') // default
const presetAddons = ref([])

function applyPreset(presetKey) {
  const p = PRESETS[presetKey]
  if (!p) return

  formProduct.category   = p.category
  formProduct.unit_label = p.unit_label

  // jangan sentuh name/slug/base_price karena itu isiannya admin
  // tapi kita generate addon bawaan:
  presetAddons.value = p.addons.map(a => ({
    code: a.code,
    label: a.label,
    amount_per_unit: a.amount_per_unit,
    is_active: a.is_active,
  }))
}

// apply preset default on mount
applyPreset(selectedPresetKey.value)

// whenever admin changes dropdown preset, re-apply
watch(selectedPresetKey, (val) => {
  applyPreset(val)
})

/**
 * Submit create:
 * - Kirim data produk
 * - Kirim preset_addons untuk langsung dibuat di DB (BizAddonPricing)
 * Setelah sukses, backend redirect ke halaman edit produk
 */
function createProduct() {
  router.post(
    route('admin.biz.store'),
    {
      ...formProduct,
      is_active: !!formProduct.is_active,
      // walau images_json & spec_json masih kosong di tahap create awal
      images_json: formProduct.images_json,
      spec_json: formProduct.spec_json,

      preset_addons: presetAddons.value,
    }
  )
}
</script>

<script>
export default {
  layout: AdminLayout,
}
</script>

<template>
  <div class="space-y-8">
    <!-- Header -->
    <div class="flex items-start justify-between flex-wrap gap-4">
      <div>
        <div class="text-lg font-semibold">Produk Business Print Baru</div>
        <div class="text-sm text-gray-500">
          Buat SKU baru untuk kategori cetak (kartu nama, brosur, dsb).
        </div>
      </div>

      <div class="text-right text-xs text-gray-500">
        <div>Setelah disimpan, Anda bisa upload foto & ubah harga addon.</div>
      </div>
    </div>

    <!-- Card Form -->
    <div class="border rounded-xl bg-white p-4 space-y-6 text-sm">
      <!-- Bagian atas: tombol simpan -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="font-semibold">Informasi Produk</div>

        <button
          class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700"
          @click="createProduct"
        >
          Simpan & Lanjutkan
        </button>
      </div>

      <!-- Preset tipe produk -->
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-gray-600 mb-1">Tipe Produk</label>
          <select
            v-model="selectedPresetKey"
            class="w-full border rounded-md px-3 py-2"
          >
            <option
              v-for="(preset, key) in PRESETS"
              :key="key"
              :value="key"
            >
              {{ preset.label }}
            </option>
          </select>
          <div class="text-[11px] text-gray-500 mt-1 leading-relaxed">
            Menentukan kategori produk, opsi yang akan ditawarkan ke customer,
            dan addon standar (misal: laminasi, lipatan, cetak 2 sisi).
          </div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Status Produk</label>
          <select
            v-model="formProduct.is_active"
            class="w-full border rounded-md px-3 py-2"
          >
            <option :value="1">Aktif (tampil di katalog)</option>
            <option :value="0">Nonaktif (disembunyikan)</option>
          </select>
        </div>
      </div>

      <!-- Nama / Slug -->
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-gray-600 mb-1">Nama Produk</label>
          <input
            v-model="formProduct.name"
            class="w-full border rounded-md px-3 py-2"
            placeholder="Contoh: Kartu Nama Premium 300gsm"
          />
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Slug (URL)</label>
          <input
            v-model="formProduct.slug"
            class="w-full border rounded-md px-3 py-2 text-xs"
            placeholder="contoh: kartu-nama-premium"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Digunakan di URL katalog: /catalog/biz/{slug}
          </div>
        </div>
      </div>

      <!-- Category / Unit label / Base price -->
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-gray-600 mb-1">Kategori (auto dari preset)</label>
          <input
            v-model="formProduct.category"
            class="w-full border rounded-md px-3 py-2 text-sm uppercase tracking-wide bg-gray-50 text-gray-600"
            readonly
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Kategori ini mengatur opsi yg muncul (lipatan? laminasi?)
          </div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Label Satuan</label>
          <input
            v-model="formProduct.unit_label"
            class="w-full border rounded-md px-3 py-2"
            placeholder="per box isi 100 pcs / per rim (100 lembar)"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Ditampilkan ke customer sebagai deskripsi paket.
          </div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Harga Dasar per Paket (Rp)</label>
          <input
            type="number"
            min="0"
            v-model.number="formProduct.base_price"
            class="w-full border rounded-md px-3 py-2"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Harga sebelum addon (laminasi, lipatan, dsb).
          </div>
        </div>
      </div>

      <!-- Preview addon yang akan dibuat otomatis -->
      <div class="border rounded-md bg-gray-50 p-3">
        <div class="font-medium text-gray-800 text-sm mb-2">
          Addon Standar (akan dibuat otomatis):
        </div>

        <div class="text-[12px] text-gray-700 space-y-2">
          <div
            v-for="(addon, idx) in presetAddons"
            :key="idx"
            class="flex flex-col md:flex-row md:items-start md:justify-between bg-white border rounded-md p-2"
          >
            <div class="text-gray-800">
              <div class="font-semibold text-xs">
                {{ addon.label }}
              </div>
              <div class="text-[10px] text-gray-500">
                Kode internal: {{ addon.code }}
              </div>
            </div>

            <div class="text-[11px] text-gray-600 mt-2 md:mt-0">
              Tambahan Rp {{ addon.amount_per_unit }} / paket
              <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] border font-medium align-middle"
                :class="addon.is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-100 text-gray-600 border-gray-200'">
                {{ addon.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
          </div>
        </div>

        <div class="text-[11px] text-gray-500 mt-3 leading-relaxed">
          Setelah produk dibuat, Anda bisa ubah harga tiap addon atau menonaktifkan addon dari halaman Edit.
        </div>
      </div>
    </div>
  </div>
</template>
