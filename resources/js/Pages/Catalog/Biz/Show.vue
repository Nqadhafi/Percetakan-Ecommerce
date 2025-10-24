<script setup>
import { reactive, ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const isLoggedIn = computed(() => !!page.props?.auth?.user)

const props = defineProps({
  product: {
    type: Object,
    required: true,
    // {
    //   id, name, slug, category,
    //   unit_label, base_price,
    //   thumbnail_url, images_json,
    //   spec: { ... }
    // }
  },
  options: {
    type: Object,
    required: true,
    // {
    //   side: ['1s','2s'],
    //   fold_type: ['none','half_fold','tri_fold', ...] or [],
    //   lamination: ['none','doff','glossy'] or [],
    //   min_qty: 1
    // }
  },
})

const fmt = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
const note = ref('')

/* ========= Gambar / Gallery / Lightbox ========= */
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

/* ========= Form state ========= */
const form = reactive({
  product_slug: props.product.slug,
  qty_unit: props.options.min_qty || 1, // berapa rim / pack
  side: props.options.side?.[0] ?? '1s', // '1s' | '2s'

  // brosur only
  fold_type: props.options.fold_type?.[0] ?? 'none',

  // kartu nama only
  lamination: props.options.lamination?.[0] ?? 'none',
})

/* ========= Quote logic ========= */
const quoting = ref(false)
const quote = ref(null)
const errorMsg = ref('')

async function doQuote() {
  errorMsg.value = ''
  quote.value = null
  quoting.value = true

  try {
    const { data } = await axios.post(route('biz.quote'), {
      product_slug: form.product_slug,
      qty_unit: Number(form.qty_unit || 1),
      side: form.side,
      fold_type: form.fold_type,
      lamination: form.lamination,
    })
    quote.value = data
  } catch (e) {
    errorMsg.value =
      e?.response?.data?.message
      ?? (e?.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(', ') : 'Gagal menghitung harga.')
  } finally {
    quoting.value = false
  }
}

let debounceTimer = null
watch(form, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(doQuote, 200)
}, { deep: true })

doQuote()

/* ========= Add to Cart ========= */
async function addToCart() {
  if (!quote.value?.ok) return
  // kirim sama formatnya kaya POP/Stiker
  await axios.post(route('cart.add'), {
    product_type: 'biz', // penting: tandai kalau ini produk BusinessPrint
    product_id: quote.value.spec_snapshot.product_id,
    name:       quote.value.spec_snapshot.product_name,
    qty:        quote.value.qty,        // jumlah paket (rim/pack)
    unit_price: quote.value.unit_price, // harga per paket setelah addon
    spec_snapshot: quote.value.spec_snapshot,
    pricing_breakdown: quote.value.breakdown,
    note: note.value?.slice(0,500) || null,
  })

  router.visit(route('cart.index'))
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold leading-snug">{{ product.name }}</h1>
        <div class="text-sm text-gray-600">
          {{ product.unit_label }}
        </div>
      </div>

      <a href="/catalog/biz" class="text-sm underline">
        ← Kembali
      </a>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
      <!-- Gallery -->
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

        <!-- Lightbox fullscreen -->
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

      <!-- Config + Quote -->
      <div class="space-y-4">
        <!-- Konfigurasi -->
        <div class="border rounded-xl bg-white p-4 space-y-4">
          <!-- Qty paket -->
          <div>
            <label class="block text-sm text-gray-600 mb-1">
              Jumlah Paket
              <span class="text-[11px] text-gray-500 font-normal">({{ product.unit_label }})</span>
            </label>
            <div class="flex items-center gap-2">
              <button
                class="px-3 py-2 border rounded-md"
                @click="form.qty_unit = Math.max(1, Number(form.qty_unit||1) - 1)"
              >
                -1
              </button>
              <input
                v-model.number="form.qty_unit"
                type="number"
                min="1"
                class="w-20 text-center border rounded-md px-3 py-2"
              />
              <button
                class="px-3 py-2 border rounded-md"
                @click="form.qty_unit = Number(form.qty_unit||1) + 1"
              >
                +1
              </button>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              Minimal 1 paket.
            </p>
          </div>

          <!-- Cetak 1 sisi / 2 sisi -->
          <div v-if="options.side?.length">
            <label class="block text-sm text-gray-600 mb-1">Cetak Sisi</label>
            <div class="grid grid-cols-2 gap-2">
              <label
                v-for="s in options.side"
                :key="s"
                class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer"
              >
                <input type="radio" :value="s" v-model="form.side" />
                <span>{{ s === '1s' ? '1 Sisi' : '2 Sisi' }}</span>
              </label>
            </div>
          </div>

          <!-- Lipatan (brosur only) -->
          <div v-if="options.fold_type && options.fold_type.length">
            <label class="block text-sm text-gray-600 mb-1">Jenis Lipatan</label>
            <select
              v-model="form.fold_type"
              class="w-full border rounded-md px-3 py-2"
            >
              <option
                v-for="f in options.fold_type"
                :key="f"
                :value="f"
              >
                {{
                  f === 'none' ? 'Tanpa Lipat'
                : f === 'half_fold' ? 'Lipat 2 (Half Fold)'
                : f === 'tri_fold' ? 'Lipat 3 (Tri Fold)'
                : f === 'z_fold' ? 'Lipat Zig-Zag'
                : f
                }}
              </option>
            </select>
          </div>

          <!-- Laminasi (kartu nama only) -->
          <div v-if="options.lamination && options.lamination.length">
            <label class="block text-sm text-gray-600 mb-1">Laminasi</label>
            <div class="grid grid-cols-2 gap-2">
              <label
                v-for="l in options.lamination"
                :key="l"
                class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer"
              >
                <input type="radio" :value="l" v-model="form.lamination" />
                <span>
                  {{
                    l === 'none' ? 'Tanpa Laminasi'
                  : l === 'doff' ? 'Laminasi Doff'
                  : l === 'glossy' ? 'Laminasi Glossy'
                  : l
                  }}
                </span>
              </label>
            </div>
          </div>

          <!-- Catatan tambahan -->
          <div>
            <label class="block text-sm text-gray-600 mb-1">Catatan (opsional)</label>
            <input
              v-model="note"
              class="w-full border rounded-md px-3 py-2"
              placeholder="Contoh: sudah siap cetak CMYK"
            />
          </div>

          <!-- Error -->
          <div v-if="errorMsg" class="text-sm text-rose-600">{{ errorMsg }}</div>
        </div>

        <!-- Quote -->
        <div class="border rounded-xl bg-white p-4">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm text-gray-600">Harga per paket</div>
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

          <div v-if="quote?.ok" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 text-sm">
            <div class="p-2 bg-gray-50 rounded-md">
              <div class="text-gray-500">Base</div>
              <div class="font-semibold">Rp {{ fmt(quote.breakdown.base) }}</div>
            </div>
            <div class="p-2 bg-gray-50 rounded-md" v-if="quote.breakdown.two_side_fee">
              <div class="text-gray-500">2 Sisi</div>
              <div class="font-semibold">Rp {{ fmt(quote.breakdown.two_side_fee) }}</div>
            </div>
            <div class="p-2 bg-gray-50 rounded-md" v-if="quote.breakdown.fold_fee">
              <div class="text-gray-500">Lipat</div>
              <div class="font-semibold">Rp {{ fmt(quote.breakdown.fold_fee) }}</div>
            </div>
            <div class="p-2 bg-gray-50 rounded-md" v-if="quote.breakdown.lamination_fee">
              <div class="text-gray-500">Laminasi</div>
              <div class="font-semibold">Rp {{ fmt(quote.breakdown.lamination_fee) }}</div>
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
            * Minimum order Rp 15.000 tetap berlaku di checkout.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
