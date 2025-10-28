<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  defaults: {
    type: Object,
    required: true,
  },
})

const form = reactive({
  name:          props.defaults.name,
  slug:          props.defaults.slug,
  category:      props.defaults.category,
  unit_label:    props.defaults.unit_label,
  base_price:    props.defaults.base_price,
  thumbnail_url: props.defaults.thumbnail_url,
  images_json:   JSON.stringify(props.defaults.images_json ?? [], null, 2),
  spec_json:     JSON.stringify(props.defaults.spec_json ?? {}, null, 2),
  is_active:     props.defaults.is_active ? 1 : 0,
})

function safeParse(str) {
  try {
    if (!str || !str.trim()) return null
    return JSON.parse(str)
  } catch(e) {
    return null
  }
}

function save() {
  router.post(route('admin.biz.store'), {
    name:          form.name,
    slug:          form.slug,
    category:      form.category,
    unit_label:    form.unit_label,
    base_price:    Number(form.base_price),
    thumbnail_url: form.thumbnail_url,
    images_json:   safeParse(form.images_json),
    spec_json:     safeParse(form.spec_json),
    is_active:     !!form.is_active,
  })
}
</script>

<script>
export default {
  layout: AdminLayout,
}
</script>

<template>
  <div class="space-y-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <div class="text-lg font-semibold">Produk BizPrint Baru</div>
        <div class="text-sm text-gray-500">Contoh: "Brosur Lipat A4 Trifold (1 Rim)"</div>
      </div>

      <div class="text-right text-xs text-gray-500">
        Setelah disimpan, lanjutkan edit untuk tambah add-on dan harga tambahan.
      </div>
    </div>

    <div class="border rounded-xl bg-white p-4 space-y-6 text-sm">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="font-semibold">Informasi Dasar Produk</div>
        <button
          class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700"
          @click="save"
        >
          Simpan & Lanjutkan
        </button>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-gray-600 mb-1">Nama Produk</label>
          <input v-model="form.name" class="w-full border rounded-md px-3 py-2" />
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Slug</label>
          <input v-model="form.slug" class="w-full border rounded-md px-3 py-2 text-xs" />
          <div class="text-[11px] text-gray-500 mt-1">Untuk URL publik.</div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Kategori</label>
          <select v-model="form.category" class="w-full border rounded-md px-3 py-2">
            <option value="kartu_nama">Kartu Nama</option>
            <option value="flyer">Flyer</option>
            <option value="brosur">Brosur Lipat</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Status Produk</label>
          <select v-model="form.is_active" class="w-full border rounded-md px-3 py-2">
            <option :value="1">Aktif (tampil di katalog)</option>
            <option :value="0">Nonaktif (disembunyikan)</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Harga Dasar (Rp)</label>
          <input
            type="number"
            min="0"
            v-model.number="form.base_price"
            class="w-full border rounded-md px-3 py-2"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Harga untuk 1 paket unit_label.
          </div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Label Paket / Unit</label>
          <input
            v-model="form.unit_label"
            class="w-full border rounded-md px-3 py-2"
            placeholder="1 Rim (500 lembar)"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Contoh: "1 Rim (500 lembar)" atau "1 Box (100 pcs)".
          </div>
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-600 mb-1">Thumbnail URL</label>
          <input
            v-model="form.thumbnail_url"
            class="w-full border rounded-md px-3 py-2 text-xs"
          />
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-600 mb-1">Gallery Images (JSON Array)</label>
          <textarea
            v-model="form.images_json"
            class="w-full border rounded-md px-3 py-2 text-xs font-mono h-24"
          ></textarea>
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-600 mb-1">Spec JSON (opsional)</label>
          <textarea
            v-model="form.spec_json"
            class="w-full border rounded-md px-3 py-2 text-xs font-mono h-24"
          ></textarea>
          <div class="text-[11px] text-gray-500 mt-1">
            Field bebas untuk kebutuhan teknis (misal "supports_double_sided": true).
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
