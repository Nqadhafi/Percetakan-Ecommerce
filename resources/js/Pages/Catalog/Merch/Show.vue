<script setup>
import { reactive, ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()
const isLoggedIn = computed(() => !!page.props?.auth?.user)

const props = defineProps({
  product: {
    type: Object,
    required: true,
    // {
    //   id, name, slug,
    //   variant_label, min_order_qty,
    //   thumbnail_url, images_json
    // }
  },
  variants: {
    type: Array,
    required: true,
    // [
    //   { code:'box_polos', label:'Box Polos' },
    //   { code:'box_custom', label:'Box Custom' }
    // ]
  },
})

const fmt = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
const note = ref('')

/* ====== Gambar / Gallery / Lightbox ====== */
const images = computed(() => {
  const arr = []
  if (props.product?.thumbnail_url) arr.push(props.product.thumbnail_url)
  const gal = Array.isArray(props.product?.images_json) ? props.product.images_json : []
  for (const g of gal) {
    if (g && !arr.includes(g)) arr.push(g)
  }
  return arr
})
const mainImg = computed(() => images.value[0] || null)

const lightboxOpen = ref(false)
const lightboxIndex = ref(0)

function openLightbox(idx = 0) {
  if (!images.value.length) return
  lightboxIndex.value = Math.min(Math.max(idx, 0), images.value.length - 1)
  lightboxOpen.value = true
}
function closeLightbox() { lightboxOpen.value = false }
function nextImg() {
  if (!images.value.length) return
  lightboxIndex.value = (lightboxIndex.value + 1) % images.value.length
}
function prevImg() {
  if (!images.value.length) return
  lightboxIndex.value = (lightboxIndex.value - 1 + images.value.length) % images.value.length
}
function onKey(e) {
  if (!lightboxOpen.value) return
  if (e.key === 'Escape') closeLightbox()
  if (e.key === 'ArrowRight') nextImg()
  if (e.key === 'ArrowLeft') prevImg()
}
onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))

/* ====== Form State ====== */
const form = reactive({
  product_slug: props.product.slug,
  variant_code: props.variants?.[0]?.code ?? '',
  qty: props.product.min_order_qty ?? 1,
})

/* ====== Quote Logic ====== */
const quoting = ref(false)
const quote = ref(null)
const errorMsg = ref('')

async function doQuote() {
  errorMsg.value = ''
  quote.value = null
  quoting.value = true

  try {
    const { data } = await axios.post(route('merch.quote'), {
      product_slug: form.product_slug,
      variant_code: form.variant_code,
      qty: Number(form.qty || 1),
    })
    quote.value = data
  } catch (e) {
    errorMsg.value =
      e?.response?.data?.message
      ?? (e?.response?.data?.errors
          ? Object.values(e.response.data.errors).flat().join(', ')
          : 'Gagal menghitung harga.')
  } finally {
    quoting.value = false
  }
}

let debTimer = null
watch(form, () => {
  clearTimeout(debTimer)
  debTimer = setTimeout(doQuote, 200)
}, { deep:true })

doQuote()

/* ====== Add To Cart ====== */
async function addToCart() {
  if (!quote.value?.ok) return

  await axios.post(route('cart.add'), {
    product_type: 'merch', // penting: tandai kategori "merch"
    product_id: quote.value.spec_snapshot.product_id,
    name:       quote.value.spec_snapshot.product_name,
    qty:        quote.value.qty,         // jumlah pcs
    unit_price: quote.value.unit_price,  // harga per pcs utk qty tsb
    spec_snapshot: quote.value.spec_snapshot,
    pricing_breakdown: quote.value.breakdown,
    note: note.value?.slice(0,500) || null,
  })

  router.visit(route('cart.index'))
}

/* ====== Helpers ====== */
function decQty() {
  const min = props.product.min_order_qty || 1
  const current = Number(form.qty || min)
  form.qty = Math.max(min, current - 1)
}
function incQty() {
  const current = Number(form.qty || 1)
  form.qty = current + 1
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold leading-snug">{{ product.name }}</h1>
        <div class="text-sm text-gray-600">
          Min. order {{ product.min_order_qty }} pcs
        </div>
      </div>

      <a href="/catalog/merch" class="text-sm underline">
        ← Kembali
      </a>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
      <!-- Gambar -->
      <div>
        <div
          class="aspect-[4/3] bg-white border rounded-xl overflow-hidden grid place-content-center cursor-zoom-in"
          @click="openLightbox(0)"
        >
          <img
            v-if="mainImg"
            :src="mainImg"
            class="w-full h-full object-cover"
            alt=""
          />
          <div v-else class="text-gray-400">No Image</div>
        </div>

        <div v-if="images.length" class="flex gap-3 overflow-x-auto mt-3">
          <button
            v-for="(img,idx) in images"
            :key="idx"
            @click="openLightbox(idx)"
            :class="[
              'w-20 h-16 border rounded-lg overflow-hidden',
              idx===lightboxIndex ? 'ring-2 ring-blue-600' : ''
            ]"
          >
            <img :src="img" class="w-full h-full object-cover" alt="" />
          </button>
        </div>

        <!-- Lightbox full -->
        <div
          v-if="lightboxOpen"
          class="fixed inset-0 bg-black/80 z-50 grid place-items-center"
          @click.self="closeLightbox"
        >
          <button class="absolute top-4 right-4 text-white text-2xl" @click="closeLightbox">✕</button>
          <button class="absolute left-4 text-white text-2xl" @click="prevImg">‹</button>

          <img
            :src="images[lightboxIndex]"
            class="max-h-[85vh] max-w-[90vw] object-contain"
          />

          <button class="absolute right-4 text-white text-2xl" @click="nextImg">›</button>
        </div>
      </div>

      <!-- Konfigurasi & Harga -->
      <div class="space-y-4">
        <!-- Pilihan & Catatan -->
        <div class="border rounded-xl bg-white p-4 space-y-4">
          <!-- Variant choice (wajib ada) -->
          <div>
            <label class="block text-sm text-gray-600 mb-1">
              {{ product.variant_label || 'Varian' }}
            </label>
            <div class="grid sm:grid-cols-2 gap-2">
              <label
                v-for="v in variants"
                :key="v.code"
                class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer"
              >
                <input
                  type="radio"
                  :value="v.code"
                  v-model="form.variant_code"
                />
                <span>{{ v.label }}</span>
              </label>
            </div>
          </div>

          <!-- Qty -->
          <div>
            <label class="block text-sm text-gray-600 mb-1">
              Jumlah
              <span class="text-[11px] text-gray-500 font-normal">
                (min {{ product.min_order_qty }} pcs)
              </span>
            </label>
            <div class="flex items-center gap-2">
              <button
                class="px-3 py-2 border rounded-md"
                @click="decQty"
              >-1</button>

              <input
                v-model.number="form.qty"
                type="number"
                :min="product.min_order_qty"
                class="w-24 text-center border rounded-md px-3 py-2"
              />

              <button
                class="px-3 py-2 border rounded-md"
                @click="incQty"
              >+1</button>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              Tidak harus kelipatan {{ product.min_order_qty }} pcs. Yang penting minimalnya terpenuhi.
            </p>
          </div>

          <!-- Catatan pelanggan -->
          <div>
            <label class="block text-sm text-gray-600 mb-1">
              Catatan (opsional)
            </label>
            <input
              v-model="note"
              class="w-full border rounded-md px-3 py-2"
              placeholder="Contoh: logo full color, file sudah siap"
            />
          </div>

          <!-- Error -->
          <div v-if="errorMsg" class="text-sm text-rose-600">
            {{ errorMsg }}
          </div>
        </div>

        <!-- Harga -->
        <div class="border rounded-xl bg-white p-4">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm text-gray-600">Harga per pcs</div>
              <div class="text-xl font-semibold">
                {{ quoting ? 'Menghitung…' : (quote?.ok ? 'Rp ' + fmt(quote.unit_price) : '—') }}
              </div>
            </div>

            <div class="text-right">
              <div class="text-sm text-gray-600">Total</div>
              <div class="text-2xl font-bold">
                {{ quoting ? '—' : (quote?.ok ? 'Rp ' + fmt(quote.total) : '—') }}
              </div>
            </div>
          </div>

          <div v-if="quote?.ok" class="grid sm:grid-cols-2 gap-3 mt-4 text-sm">
            <div class="p-2 bg-gray-50 rounded-md">
              <div class="text-gray-500">Base</div>
              <div class="font-semibold">Rp {{ fmt(quote.breakdown.base) }}</div>
            </div>
          </div>

          <div class="mt-4 flex items-center justify-end gap-2">
            <button
              class="px-4 py-2 rounded-md border hover:bg-gray-50"
              @click="doQuote"
            >
              Hitung Ulang
            </button>

            <button
              class="px-4 py-2 rounded-md bg-blue-600 text-white disabled:opacity-60"
              :disabled="!quote?.ok || quoting"
              @click="addToCart"
            >
              Tambah ke Keranjang
            </button>
          </div>

          <p class="text-xs text-gray-500 mt-2">
            * Minimum order Rp 15.000 tetap akan dicek saat checkout.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
