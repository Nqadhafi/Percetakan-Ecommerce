<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  defaults: {
    type: Object,
    required: true,
    // {
    //   name, slug, is_active, min_order_qty,
    //   variant_label, thumbnail_url, images_json:[]
    // }
  },
})

const formProduct = reactive({
  name:          props.defaults.name,
  slug:          props.defaults.slug,
  is_active:     props.defaults.is_active ? 1 : 0,
  min_order_qty: props.defaults.min_order_qty,
  variant_label: props.defaults.variant_label,
  thumbnail_url: props.defaults.thumbnail_url,
  images_json:   props.defaults.images_json,
})

function createProduct() {
  router.post(
    route('admin.merch.store'),
    {
      ...formProduct,
      is_active: !!formProduct.is_active,
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
    <div class="flex items-start justify-between flex-wrap gap-4">
      <div>
        <div class="text-lg font-semibold">Produk Merchandise Baru</div>
        <div class="text-sm text-gray-500">
          Buat SKU baru untuk kategori merchandise (mug, tumbler, pin, dll)
        </div>
      </div>

      <div class="text-right text-xs text-gray-500">
        <div>Setelah disimpan, Anda bisa menambahkan varian & tier harga.</div>
      </div>
    </div>

    <div class="border rounded-xl bg-white p-4 space-y-4">
      <div class="flex items-center justify-between">
        <div class="font-semibold">Informasi Produk</div>
        <button
          class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700"
          @click="createProduct"
        >
          Simpan & Lanjutkan
        </button>
      </div>

      <div class="grid md:grid-cols-2 gap-4 text-sm">
        <div>
          <label class="block text-gray-600 mb-1">Nama Produk</label>
          <input v-model="formProduct.name" class="w-full border rounded-md px-3 py-2" />
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Slug</label>
          <input v-model="formProduct.slug" class="w-full border rounded-md px-3 py-2 text-xs" />
          <div class="text-[11px] text-gray-500 mt-1">Digunakan di URL katalog.</div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Status Produk</label>
          <select v-model="formProduct.is_active" class="w-full border rounded-md px-3 py-2">
            <option :value="1">Aktif (ditampilkan di katalog)</option>
            <option :value="0">Nonaktif (disembunyikan)</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Minimal Order (pcs)</label>
          <input
            type="number"
            min="1"
            v-model.number="formProduct.min_order_qty"
            class="w-full border rounded-md px-3 py-2"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Contoh: Pin min 5 pcs, Mug min 1 pcs.
          </div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Label Field Varian</label>
          <input
            v-model="formProduct.variant_label"
            class="w-full border rounded-md px-3 py-2"
            placeholder="misal: Packaging / Ukuran / Tipe Box"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Ini akan tampil di frontend saat customer pilih varian.
          </div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Thumbnail URL</label>
          <input
            v-model="formProduct.thumbnail_url"
            class="w-full border rounded-md px-3 py-2 text-xs"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Gambar utama katalog.
          </div>
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-600 mb-1">Gallery Images (JSON)</label>
          <textarea
            v-model="(formProduct.images_json)"
            class="w-full border rounded-md px-3 py-2 text-xs font-mono h-24"
          ></textarea>
          <div class="text-[11px] text-gray-500 mt-1">
            Array URL gambar (opsional, bisa diedit lagi nanti).
          </div>
        </div>
      </div>
    </div>

  </div>
</template>
