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
    //   id, name, slug, is_active,
    //   min_order_qty, variant_label,
    //   thumbnail_url, images_json:[]
    // }
  },
  variants: {
    type: Array,
    required: true,
    // each:
    // {
    //   id, code, label, is_active,
    //   tiers: [{id,min_qty,unit_price}]
    // }
  },
})

/**
 * FORM: EDIT PRODUK
 */
const formProduct = reactive({
  name:          props.product.name,
  slug:          props.product.slug,
  is_active:     props.product.is_active ? 1 : 0,
  min_order_qty: props.product.min_order_qty,
  variant_label: props.product.variant_label || '',
  thumbnail_url: props.product.thumbnail_url || '',
  images_json:   Array.isArray(props.product.images_json)
    ? [...props.product.images_json]
    : [],
})

function saveProduct() {
  router.patch(
    route('admin.merch.updateProduct', props.product.id),
    {
      ...formProduct,
      is_active: !!formProduct.is_active,
      images_json: formProduct.images_json,
    },
    { preserveScroll: true }
  )
}

/**
 * MEDIA UPLOAD
 * - sama konsepnya kayak biz, tapi folder='merch'
 * - thumbnail satuan
 * - gallery bisa banyak
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
    fd.append('folder', 'merch') // namespace merch

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
    fd.append('folder', 'merch')

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
 * FORM: TAMBAH VARIAN BARU
 */
const newVariant = reactive({
  code: '',
  label: '',
  is_active: 1,
})

function addVariant() {
  router.post(
    route('admin.merch.variant.store', props.product.id),
    {
      code: newVariant.code,
      label: newVariant.label,
      is_active: !!newVariant.is_active,
    },
    { preserveScroll: true }
  )
}

/**
 * UPDATE VARIAN EXISTING
 */
function saveVariant(v) {
  router.patch(
    route('admin.merch.variant.update', v.id),
    {
      code: v.code,
      label: v.label,
      is_active: !!v.is_active,
    },
    { preserveScroll: true }
  )
}

/**
 * TAMBAH TIER BARU UNTUK VARIAN TERTENTU
 */
function addTier(v, draftTier) {
  router.post(
    route('admin.merch.tier.store', v.id),
    {
      min_qty:    draftTier.min_qty,
      unit_price: draftTier.unit_price,
    },
    { preserveScroll: true }
  )
}

/**
 * UPDATE TIER EXISTING
 */
function saveTier(tier) {
  router.patch(
    route('admin.merch.tier.update', tier.id),
    {
      min_qty: tier.min_qty,
      unit_price: tier.unit_price,
    },
    { preserveScroll: true }
  )
}

// helper
const fmt = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
</script>

<script>
export default {
  layout: AdminLayout,
}
</script>

<template>
  <div class="space-y-8">
    <!-- HEADER -->
    <div class="flex items-start justify-between flex-wrap gap-4">
      <div>
        <div class="text-lg font-semibold">
          Edit Merchandise
        </div>
        <div class="text-sm text-gray-500">
          {{ product.name }} (ID: {{ product.id }})
        </div>
      </div>

      <div class="text-right text-xs text-gray-500">
        <div>Min Order: {{ product.min_order_qty }} pcs</div>
        <div>Status: <b>{{ product.is_active ? 'Aktif' : 'Nonaktif' }}</b></div>
      </div>
    </div>

    <!-- SECTION: DATA PRODUK -->
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

      <div class="grid md:grid-cols-2 gap-4 text-sm">
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
            Digunakan untuk URL product.
          </div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Status Produk</label>
          <select
            v-model="formProduct.is_active"
            class="w-full border rounded-md px-3 py-2"
          >
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
            placeholder="misal: Packaging, Ukuran"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Ini label yang muncul di frontend (misal 'Ukuran', 'Packaging').
          </div>
        </div>

        <!-- THUMBNAIL UPLOAD -->
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
                Ini akan tampil sebagai gambar utama di katalog.
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

        <!-- GALLERY -->
        <div class="md:col-span-2">
          <label class="block text-gray-600 mb-1">Galeri Foto</label>

          <div class="flex flex-wrap gap-4 text-xs">
            <!-- foto yang sudah ada -->
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
            Foto-foto tambahan akan tampil di galeri produk (carousel/lightbox).
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: VARIAN & TIER -->
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div class="font-semibold">Varian & Tier Harga</div>
      </div>

      <!-- Form tambah varian baru -->
      <div class="border rounded-xl bg-white p-4 text-sm space-y-3">
        <div class="font-medium">Tambah Varian Baru</div>

        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="block text-gray-600 mb-1">Kode Varian</label>
            <input
              v-model="newVariant.code"
              class="w-full border rounded-md px-3 py-2"
              placeholder="contoh: box_polos / uk_4_4 / tanpa_box"
            />
            <div class="text-[11px] text-gray-500 mt-1">
              Dipakai di FE untuk quote / pilihan customer.
            </div>
          </div>

          <div>
            <label class="block text-gray-600 mb-1">Label Tampilan</label>
            <input
              v-model="newVariant.label"
              class="w-full border rounded-md px-3 py-2"
              placeholder="contoh: Box Polos / Ukuran 4.4 cm"
            />
          </div>

          <div>
            <label class="block text-gray-600 mb-1">Status</label>
            <select
              v-model="newVariant.is_active"
              class="w-full border rounded-md px-3 py-2"
            >
              <option :value="1">Aktif</option>
              <option :value="0">Nonaktif</option>
            </select>
          </div>
        </div>

        <div class="text-right">
          <button
            class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700"
            @click="addVariant"
          >
            Tambah Varian
          </button>
        </div>
      </div>

      <!-- Semua varian -->
      <div
        v-for="v in variants"
        :key="v.id"
        class="border rounded-xl bg-white"
      >
        <div class="p-4 border-b flex flex-wrap items-start justify-between gap-4 text-sm">
          <div class="min-w-0 flex-1">
            <div class="font-semibold text-gray-800">
              Varian: {{ v.label }}
              <span
                class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] border font-medium align-middle"
                :class="v.is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-100 text-gray-600 border-gray-200'"
              >
                {{ v.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>

            <div class="text-[11px] text-gray-500 break-all">
              code: {{ v.code }}
            </div>
          </div>

          <!-- Form update varian -->
          <div class="w-full md:w-auto md:min-w-[280px]">
            <div class="grid grid-cols-1 gap-2">
              <input
                v-model="v.code"
                class="border rounded-md px-3 py-2 text-xs w-full"
                placeholder="code"
              />
              <input
                v-model="v.label"
                class="border rounded-md px-3 py-2 text-xs w-full"
                placeholder="label"
              />
              <select
                v-model="v.is_active"
                class="border rounded-md px-3 py-2 text-xs w-full"
              >
                <option :value="1">Aktif</option>
                <option :value="0">Nonaktif</option>
              </select>

              <button
                class="px-3 py-2 rounded-md bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700"
                @click="saveVariant(v)"
              >
                Simpan Varian
              </button>
            </div>
          </div>
        </div>

        <!-- Tier harga -->
        <div class="p-4 text-sm space-y-4">
          <div class="font-medium">Tier Harga (per pcs)</div>

          <div class="overflow-x-auto border rounded-md">
            <table class="w-full text-xs">
              <thead class="bg-gray-50 text-gray-600 border-b">
                <tr>
                  <th class="text-left px-3 py-2 whitespace-nowrap">Min Qty</th>
                  <th class="text-left px-3 py-2 whitespace-nowrap">Harga per pcs</th>
                  <th class="px-3 py-2 whitespace-nowrap text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="t in v.tiers"
                  :key="t.id"
                  class="border-b last:border-b-0"
                >
                  <td class="px-3 py-2">
                    <input
                      type="number"
                      min="1"
                      v-model.number="t.min_qty"
                      class="border rounded-md px-2 py-1 w-24 text-xs"
                    />
                  </td>
                  <td class="px-3 py-2">
                    <div class="flex items-center gap-1">
                      <span class="text-gray-500">Rp</span>
                      <input
                        type="number"
                        min="0"
                        v-model.number="t.unit_price"
                        class="border rounded-md px-2 py-1 w-28 text-xs"
                      />
                    </div>
                    <div class="text-[10px] text-gray-500 mt-1">
                      Contoh: {{ fmt(t.unit_price) }} /pcs
                    </div>
                  </td>
                  <td class="px-3 py-2 text-right">
                    <button
                      class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-[11px] font-semibold hover:bg-blue-700"
                      @click="saveTier(t)"
                    >
                      Simpan Tier
                    </button>
                  </td>
                </tr>

                <tr v-if="!v.tiers.length">
                  <td colspan="3" class="px-3 py-4 text-center text-gray-500 text-xs">
                    Belum ada tier harga.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Form tambah tier baru -->
          <div class="border rounded-md p-3 bg-gray-50 text-xs space-y-2 md:flex md:items-end md:gap-3 md:space-y-0">
            <div>
              <label class="block text-gray-600 mb-1">Min Qty</label>
              <input
                type="number"
                min="1"
                v-model.number="(v._draftTierMinQty)"
                class="border rounded-md px-2 py-1 w-24"
                placeholder="1"
              />
            </div>

            <div>
              <label class="block text-gray-600 mb-1">Harga /pcs (Rp)</label>
              <input
                type="number"
                min="0"
                v-model.number="(v._draftTierUnit)"
                class="border rounded-md px-2 py-1 w-28"
                placeholder="50000"
              />
            </div>

            <div class="md:ml-auto">
              <button
                class="px-3 py-2 rounded-md bg-blue-600 text-white font-semibold hover:bg-blue-700 w-full md:w-auto"
                @click="addTier(v, { min_qty: v._draftTierMinQty, unit_price: v._draftTierUnit })"
              >
                Tambah Tier
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>
