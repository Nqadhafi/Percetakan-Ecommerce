<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  product: {
    type: Object,
    required: true,
    // {
    //   id,name,slug,category,unit_label,base_price,
    //   thumbnail_url,images_json,spec_json,is_active
    // }
  },
  addons: {
    type: Array,
    required: true,
    // [{id, code, label, amount_per_unit, is_active}]
  },
})

// ---- FORM PRODUK ----
const formProduct = reactive({
  name:          props.product.name,
  slug:          props.product.slug,
  category:      props.product.category,
  unit_label:    props.product.unit_label,
  base_price:    props.product.base_price,
  thumbnail_url: props.product.thumbnail_url || '',
  images_json:   JSON.stringify(props.product.images_json ?? [], null, 2),
  spec_json:     JSON.stringify(props.product.spec_json ?? {}, null, 2),
  is_active:     props.product.is_active ? 1 : 0,
})

function safeParse(str) {
  try {
    if (!str || !str.trim()) return null
    return JSON.parse(str)
  } catch(e) {
    return null
  }
}

function saveProduct() {
  router.patch(
    route('admin.biz.updateProduct', props.product.id),
    {
      name:          formProduct.name,
      slug:          formProduct.slug,
      category:      formProduct.category,
      unit_label:    formProduct.unit_label,
      base_price:    Number(formProduct.base_price),
      thumbnail_url: formProduct.thumbnail_url,
      images_json:   safeParse(formProduct.images_json),
      spec_json:     safeParse(formProduct.spec_json),
      is_active:     !!formProduct.is_active,
    },
    { preserveScroll: true }
  )
}

// ---- ADD-ON: FORM ADD NEW ----
const newAddon = reactive({
  code: '',
  label: '',
  amount_per_unit: 0,
  is_active: 1,
})

function addAddon() {
  router.post(
    route('admin.biz.addon.store', props.product.id),
    {
      code: newAddon.code,
      label: newAddon.label,
      amount_per_unit: Number(newAddon.amount_per_unit),
      is_active: !!newAddon.is_active,
    },
    { preserveScroll: true }
  )
}

// ---- ADD-ON: UPDATE EXISTING ----
function saveAddon(a) {
  router.patch(
    route('admin.biz.addon.update', a.id),
    {
      code: a.code,
      label: a.label,
      amount_per_unit: Number(a.amount_per_unit),
      is_active: !!a.is_active,
    },
    { preserveScroll: true }
  )
}

const fmtMoney = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
</script>

<script>
export default {
  layout: AdminLayout,
}
</script>

<template>
  <div class="space-y-8">
    <!-- HEADER -->
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <div class="text-lg font-semibold">Edit BizPrint</div>
        <div class="text-sm text-gray-500">
          {{ product.name }} ({{ product.category.replace('_',' ') }})
        </div>
      </div>

      <div class="text-right text-xs text-gray-500">
        <div>Status: <b>{{ product.is_active ? 'Aktif' : 'Nonaktif' }}</b></div>
        <div>Base Price: Rp {{ fmtMoney(product.base_price) }}</div>
        <div>Unit Label: {{ product.unit_label }}</div>
      </div>
    </div>

    <!-- CARD: INFO PRODUK -->
    <div class="border rounded-xl bg-white p-4 space-y-6 text-sm">
      <div class="flex flex-wrap items-start justify-between gap-4">
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
          <input v-model="formProduct.name" class="w-full border rounded-md px-3 py-2" />
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Slug</label>
          <input v-model="formProduct.slug" class="w-full border rounded-md px-3 py-2 text-xs" />
          <div class="text-[11px] text-gray-500 mt-1">URL publik</div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Kategori</label>
          <select v-model="formProduct.category" class="w-full border rounded-md px-3 py-2">
            <option value="kartu_nama">Kartu Nama</option>
            <option value="flyer">Flyer</option>
            <option value="brosur">Brosur Lipat</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Status Produk</label>
          <select v-model="formProduct.is_active" class="w-full border rounded-md px-3 py-2">
            <option :value="1">Aktif (tampil)</option>
            <option :value="0">Nonaktif (hidden)</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Harga Dasar (Rp)</label>
          <input
            type="number"
            min="0"
            v-model.number="formProduct.base_price"
            class="w-full border rounded-md px-3 py-2"
          />
          <div class="text-[11px] text-gray-500 mt-1">
            Harga untuk 1 {{ formProduct.unit_label }} (tanpa add-on).
          </div>
        </div>

        <div>
          <label class="block text-gray-600 mb-1">Unit Label</label>
          <input
            v-model="formProduct.unit_label"
            class="w-full border rounded-md px-3 py-2"
            placeholder="1 Rim (500 lembar)"
          />
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-600 mb-1">Thumbnail URL</label>
          <input
            v-model="formProduct.thumbnail_url"
            class="w-full border rounded-md px-3 py-2 text-xs"
          />
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-600 mb-1">Gallery Images (JSON Array)</label>
          <textarea
            v-model="formProduct.images_json"
            class="w-full border rounded-md px-3 py-2 text-xs font-mono h-24"
          ></textarea>
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-600 mb-1">Spec JSON (opsional)</label>
          <textarea
            v-model="formProduct.spec_json"
            class="w-full border rounded-md px-3 py-2 text-xs font-mono h-24"
          ></textarea>
          <div class="text-[11px] text-gray-500 mt-1">
            Field bebas untuk catatan teknis:
            { "supports_double_sided": true, "supports_lamination": false }
          </div>
        </div>
      </div>
    </div>

    <!-- CARD: ADD-ONS -->
    <div class="space-y-6">
      <div class="font-semibold">Add-on & Biaya Tambahan per Paket</div>

      <!-- Add-on baru -->
      <div class="border rounded-xl bg-white p-4 space-y-3 text-sm">
        <div class="font-medium">Tambah Add-on Baru</div>

        <div class="grid md:grid-cols-4 gap-4">
          <div>
            <label class="block text-gray-600 mb-1">Kode Add-on</label>
            <input
              v-model="newAddon.code"
              class="w-full border rounded-md px-3 py-2 text-xs"
              placeholder="contoh: 2sisi, laminasi_doff, trifold"
            />
            <div class="text-[11px] text-gray-500 mt-1">
              Harus unik di produk ini.
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-gray-600 mb-1">Label Tampilan</label>
            <input
              v-model="newAddon.label"
              class="w-full border rounded-md px-3 py-2 text-xs"
              placeholder="Cetak 2 sisi"
            />
          </div>

          <div>
            <label class="block text-gray-600 mb-1">Status</label>
            <select v-model="newAddon.is_active" class="w-full border rounded-md px-3 py-2 text-xs">
              <option :value="1">Aktif</option>
              <option :value="0">Nonaktif</option>
            </select>
          </div>

          <div>
            <label class="block text-gray-600 mb-1">Biaya Tambahan (Rp)</label>
            <input
              type="number"
              min="0"
              v-model.number="newAddon.amount_per_unit"
              class="w-full border rounded-md px-3 py-2 text-xs"
              placeholder="20000"
            />
            <div class="text-[11px] text-gray-500 mt-1">
              Ditambahkan per paket (per {{ formProduct.unit_label }})
            </div>
          </div>
        </div>

        <div class="text-right">
          <button
            class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700"
            @click="addAddon"
          >
            Tambah Add-on
          </button>
        </div>
      </div>

      <!-- List add-on eksisting -->
      <div class="border rounded-xl bg-white p-4 text-sm space-y-4">
        <div class="font-medium">Daftar Add-on</div>

        <div class="overflow-x-auto border rounded-md">
          <table class="w-full text-xs">
            <thead class="bg-gray-50 text-gray-600 border-b">
              <tr>
                <th class="text-left px-3 py-2">Kode</th>
                <th class="text-left px-3 py-2">Label</th>
                <th class="text-left px-3 py-2 whitespace-nowrap">+ Harga /paket</th>
                <th class="text-left px-3 py-2">Status</th>
                <th class="px-3 py-2 text-right">Aksi</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="a in addons"
                :key="a.id"
                class="border-b last:border-b-0"
              >
                <td class="px-3 py-2 align-top">
                  <input
                    v-model="a.code"
                    class="border rounded-md px-2 py-1 w-full text-[11px]"
                  />
                </td>

                <td class="px-3 py-2 align-top">
                  <input
                    v-model="a.label"
                    class="border rounded-md px-2 py-1 w-full text-[11px]"
                  />
                </td>

                <td class="px-3 py-2 align-top">
                  <div class="flex items-center gap-1">
                    <span class="text-gray-500">Rp</span>
                    <input
                      type="number"
                      min="0"
                      v-model.number="a.amount_per_unit"
                      class="border rounded-md px-2 py-1 w-24 text-[11px]"
                    />
                  </div>
                  <div class="text-[10px] text-gray-500 mt-1">
                    per {{ formProduct.unit_label }}
                  </div>
                </td>

                <td class="px-3 py-2 align-top">
                  <select
                    v-model="a.is_active"
                    class="border rounded-md px-2 py-1 w-full text-[11px]"
                  >
                    <option :value="1">Aktif</option>
                    <option :value="0">Nonaktif</option>
                  </select>
                </td>

                <td class="px-3 py-2 align-top text-right">
                  <button
                    class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-[11px] font-semibold hover:bg-blue-700"
                    @click="saveAddon(a)"
                  >
                    Simpan
                  </button>
                </td>
              </tr>

              <tr v-if="!addons.length">
                <td colspan="5" class="px-3 py-4 text-center text-gray-500 text-xs">
                  Belum ada add-on harga untuk produk ini.
                  Tambahkan add-on di atas (contoh: "Cetak 2 sisi", "Laminasi Doff").
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>

  </div>
</template>
