<script setup>
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  product: {
    type: Object,
    required: true,
    // {
    //   id, name, slug,
    //   category, unit_label,
    //   base_price,
    //   thumbnail_url,
    //   images_json: [],
    //   spec_json: {},   // <- object, contoh {Ukuran:"A4", Bahan:"Art Paper"}
    //   is_active
    // }
  },
  addons: {
    type: Array,
    required: true,
    // [
    //   { id, code, label, amount_per_unit, is_active }
    // ]
  },
  hint_codes: {
    type: Object,
    default: () => ({}),
  },
})

/**
 * STATE: produk
 */
const formProduct = reactive({
  name:          props.product.name,
  slug:          props.product.slug,
  category:      props.product.category,
  unit_label:    props.product.unit_label,
  base_price:    props.product.base_price,
  thumbnail_url: props.product.thumbnail_url || '',
  images_json:   Array.isArray(props.product.images_json) ? [...props.product.images_json] : [],
  is_active:     props.product.is_active ? 1 : 0,
})

// SPEC rows: kita convert object -> array pasangan key/value
const specRows = ref(
  Object.entries(props.product.spec_json || {}).map(([key, value]) => ({
    key,
    value,
  }))
)

// helper: build spec_json object buat dikirim balik
function buildSpecObject() {
  const obj = {}
  for (const row of specRows.value) {
    if (!row.key) continue
    obj[row.key] = row.value || ''
  }
  return obj
}

/**
 * Upload thumbnail & galeri
 */
const uploadingThumb = ref(false)
const uploadingGallery = ref(false)

async function handleUploadThumbnail(e) {
  const file = e.target.files?.[0]
  if (!file) return

  uploadingThumb.value = true
  try {
    const fd = new FormData()
    fd.append('file', file)
    fd.append('folder', 'biz') // important: namespace untuk biz

    const { data } = await axios.post(route('admin.media.upload'), fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    if (data?.url) {
      formProduct.thumbnail_url = data.url
    }
  } finally {
    uploadingThumb.value = false
  }
}

async function handleUploadGallery(e) {
  const file = e.target.files?.[0]
  if (!file) return

  uploadingGallery.value = true
  try {
    const fd = new FormData()
    fd.append('file', file)
    fd.append('folder', 'biz')

    const { data } = await axios.post(route('admin.media.upload'), fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    if (data?.url) {
      formProduct.images_json.push(data.url)
    }
  } finally {
    uploadingGallery.value = false
  }
}

function removeGalleryImage(idx) {
  formProduct.images_json.splice(idx, 1)
}

/**
 * Simpan perubahan produk
 */
function saveProduct() {
  router.patch(
    route('admin.biz.updateProduct', props.product.id),
    {
      ...formProduct,
      is_active: !!formProduct.is_active,
      images_json: formProduct.images_json,
      spec_json: buildSpecObject(),
    },
    { preserveScroll: true }
  )
}

/**
 * STATE & ACTIONS: Addons
 */
const addonList = reactive(
  props.addons.map(a => ({
    id: a.id,
    code: a.code,
    label: a.label,
    amount_per_unit: a.amount_per_unit,
    is_active: a.is_active ? 1 : 0,
  }))
)

function saveAddon(a) {
  router.patch(
    route('admin.biz.addon.update', a.id),
    {
      code: a.code,
      label: a.label,
      amount_per_unit: a.amount_per_unit,
      is_active: !!a.is_active,
    },
    { preserveScroll: true }
  )
}

function disableAddon(a) {
  router.patch(
    route('admin.biz.addon.disable', a.id),
    {},
    { preserveScroll: true }
  )
}

// harga display helper
const fmt = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
</script>

<script>
export default {
  layout: AdminLayout,
}
</script>

<template>
  <div class="space-y-8 text-sm">
    <!-- HEADER -->
    <div class="flex items-start justify-between flex-wrap gap-4">
      <div>
        <div class="text-lg font-semibold">
          Edit Business Print
        </div>
        <div class="text-sm text-gray-500">
          {{ product.name }} (ID: {{ product.id }})
        </div>
      </div>

      <div class="text-right text-xs text-gray-500">
        <div>Kategori: <b class="uppercase">{{ product.category }}</b></div>
        <div>Status:
          <b>{{ product.is_active ? 'Aktif' : 'Nonaktif' }}</b>
        </div>
      </div>
    </div>

    <!-- ========== SECTION: PRODUK UTAMA ========== -->
    <div class="border rounded-xl bg-white p-4 space-y-6">
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="font-semibold">Informasi Produk</div>
        <button
          class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700"
          @click="saveProduct"
        >
          Simpan Produk
        </button>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-gray-600 mb-1">Nama Produk</label>
          <input
            v-model="formProduct.name"
            class="w-full border rounded-md px-3 py-2"
          />
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Slug</label>
          <input
            v-model="formProduct.slug"
            class="w-full border rounded-md px-3 py-2 text-xs"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Digunakan di URL katalog.
          </div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Kategori</label>
          <input
            v-model="formProduct.category"
            class="w-full border rounded-md px-3 py-2 text-sm uppercase tracking-wide bg-gray-50 text-gray-600"
            readonly
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Menentukan opsi apa yang muncul di halaman customer.
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

        <div>
          <label class="block text-gray-600 mb-1">Label Satuan</label>
          <input
            v-model="formProduct.unit_label"
            class="w-full border rounded-md px-3 py-2"
            placeholder="per box isi 100 pcs / per rim (100 lembar)"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Customer akan lihat ini.
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
            Rp {{ fmt(formProduct.base_price) }} / paket sebelum addon.
          </div>
        </div>
      </div>

      <!-- Upload Thumbnail -->
      <div>
        <label class="block text-gray-600 mb-1">Gambar Utama (Thumbnail)</label>

        <div class="flex items-start gap-4">
          <div class="w-24 h-24 border rounded-md bg-gray-50 overflow-hidden flex items-center justify-center text-[11px] text-gray-400">
            <img
              v-if="formProduct.thumbnail_url"
              :src="formProduct.thumbnail_url"
              class="w-full h-full object-cover"
            />
            <span v-else>Belum ada</span>
          </div>

          <div class="text-xs text-gray-600 space-y-2">
            <div>
              <input
                type="file"
                accept="image/*"
                class="block text-xs"
                @change="handleUploadThumbnail"
              />
            </div>

            <div class="text-[11px] text-gray-500 leading-snug">
              Ini gambar utama yang tampil di katalog.
            </div>

            <div
              v-if="uploadingThumb"
              class="text-[11px] text-blue-600"
            >
              Uploading...
            </div>
          </div>
        </div>
      </div>

      <!-- Gallery -->
      <div>
        <label class="block text-gray-600 mb-1">Galeri Foto</label>

        <div class="flex flex-wrap gap-4 text-xs">
          <!-- daftar foto -->
          <div
            v-for="(url, idx) in formProduct.images_json"
            :key="idx"
            class="w-24"
          >
            <div class="w-24 h-24 border rounded-md bg-gray-50 overflow-hidden flex items-center justify-center text-[11px] text-gray-400">
              <img
                v-if="url"
                :src="url"
                class="w-full h-full object-cover"
              />
              <span v-else>no img</span>
            </div>

            <button
              class="mt-1 w-full px-2 py-1 rounded-md bg-red-100 text-red-600 text-[10px]"
              @click="removeGalleryImage(idx)"
            >
              Hapus
            </button>
          </div>

          <!-- upload baru -->
          <div class="relative w-24 h-24 border-2 border-dashed border-gray-300 rounded-md flex flex-col items-center justify-center text-center text-[11px] text-gray-500">
            <div class="text-[20px] leading-none mb-1">＋</div>
            <div>Tambah</div>

            <input
              type="file"
              accept="image/*"
              class="absolute inset-0 opacity-0 cursor-pointer"
              @change="handleUploadGallery"
            />

            <div
              v-if="uploadingGallery"
              class="absolute bottom-1 text-[10px] text-blue-600"
            >
              Uploading...
            </div>
          </div>
        </div>

        <div class="text-[11px] text-gray-500 leading-snug mt-2">
          Foto-foto ini tampil di galeri & lightbox di halaman produk customer.
        </div>
      </div>

      <!-- Spec teknis -->
      <div>
        <label class="block text-gray-600 mb-1">Spesifikasi Teknis</label>

        <div class="space-y-2">
          <div
            v-for="(row, idx) in specRows"
            :key="idx"
            class="grid grid-cols-2 gap-2 text-xs"
          >
            <input
              v-model="row.key"
              class="border rounded-md px-3 py-2"
              placeholder="Contoh: Ukuran"
            />
            <div class="flex gap-2">
              <input
                v-model="row.value"
                class="flex-1 border rounded-md px-3 py-2"
                placeholder="Contoh: A4"
              />
              <button
                class="px-2 py-2 rounded-md bg-red-100 text-red-600"
                @click="specRows.splice(idx,1)"
              >
                ✕
              </button>
            </div>
          </div>

          <button
            class="px-2 py-1.5 rounded-md bg-gray-100 text-gray-700 text-[11px]"
            @click="specRows.push({ key: '', value: '' })"
          >
            + Tambah Baris
          </button>

          <div class="text-[11px] text-gray-500 leading-snug">
            Contoh:
            Ukuran = A4,
            Bahan = Art Paper 150gsm,
            Warna = Full Color.
          </div>
        </div>
      </div>
    </div>

    <!-- ========== SECTION: ADDONS ========== -->
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div class="font-semibold">Addon & Biaya Tambahan</div>
      </div>

      <!-- daftar addon -->
      <div
        v-for="a in addonList"
        :key="a.id"
        class="border rounded-xl bg-white"
      >
        <div class="p-4 border-b flex flex-wrap items-start justify-between gap-4 text-sm">
          <div class="min-w-0 flex-1">
            <div class="font-semibold text-gray-800">
              {{ a.label }}
              <span
                class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] border font-medium align-middle"
                :class="a.is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-100 text-gray-600 border-gray-200'"
              >
                {{ a.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>

            <div class="text-[11px] text-gray-500 break-all">
              Kode internal: {{ a.code }}
            </div>

            <div class="text-[11px] text-gray-500">
              Biaya tambahan saat order:
              <b>Rp {{ fmt(a.amount_per_unit) }} / paket</b>
            </div>

            <div
              v-if="hint_codes[a.code]"
              class="text-[11px] text-gray-400 italic mt-1"
            >
              {{ hint_codes[a.code] }}
            </div>
          </div>

          <!-- editor addon -->
          <div class="w-full md:w-auto md:min-w-[280px]">
            <div class="grid grid-cols-1 gap-2 text-xs">
              <label class="block">
                <div class="text-gray-600 mb-1 text-[11px]">Label Tampilan</div>
                <input
                  v-model="a.label"
                  class="border rounded-md px-3 py-2 text-xs w-full"
                />
              </label>

              <label class="block">
                <div class="text-gray-600 mb-1 text-[11px]">Biaya Tambahan /paket (Rp)</div>
                <input
                  type="number"
                  min="0"
                  v-model.number="a.amount_per_unit"
                  class="border rounded-md px-3 py-2 text-xs w-full"
                />
              </label>

              <label class="block">
                <div class="text-gray-600 mb-1 text-[11px]">Status</div>
                <select
                  v-model="a.is_active"
                  class="border rounded-md px-3 py-2 text-xs w-full"
                >
                  <option :value="1">Aktif</option>
                  <option :value="0">Nonaktif</option>
                </select>
              </label>

              <div class="flex flex-wrap justify-end gap-2">
                <button
                  class="px-3 py-2 rounded-md bg-blue-600 text-white text-[11px] font-semibold hover:bg-blue-700"
                  @click="saveAddon(a)"
                >
                  Simpan Addon
                </button>

                <button
                  class="px-3 py-2 rounded-md bg-gray-200 text-gray-700 text-[11px] font-medium hover:bg-gray-300"
                  @click="disableAddon(a)"
                >
                  Nonaktifkan
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="!addonList.length"
        class="text-center text-gray-500 text-xs border rounded-xl bg-white p-6"
      >
        Belum ada addon biaya tambahan.
      </div>
    </div>
  </div>
</template>
