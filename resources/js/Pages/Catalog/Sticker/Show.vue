<script setup>
import { reactive, ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()
const isLoggedIn = computed(() => !!page.props?.auth?.user)

const props = defineProps({
  product: { type: Object, required: true }, // {id,name,slug,thumbnail_url,images_json}
  options: { type: Object, required: true }, // {materials, laminations, finishes, min_qty, step_qty}
  specs:   { type: Array,  required: true }, // [{material, supports_lamination, min_qty, tiers:[...]}]
})

const note = ref('')
const fmt = (n) => new Intl.NumberFormat('id-ID').format(n ?? 0)

/* ========= Gambar: thumbnail → gallery ========= */
const images = computed(() => {
  const arr = []
  if (props.product?.thumbnail_url) arr.push(props.product.thumbnail_url)
  const gal = Array.isArray(props.product?.images_json) ? props.product.images_json : []
  for (const g of gal) { if (g && !arr.includes(g)) arr.push(g) }
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

/* ========= Form (tanpa shape, A3/A3+) ========= */
const form = reactive({
  product_slug: props.product.slug,
  material: props.options.materials?.[0] ?? '',
  lamination: props.options.laminations?.[0] ?? 'none',
  finish: props.options.finishes?.[0] ?? 'none',
  qty: props.options.min_qty || 1,
})

const minQty  = computed(() => props.options.min_qty  || 1)
const stepQty = computed(() => props.options.step_qty || 1)

const all = props.specs
const uniq = (arr) => Array.from(new Set(arr))
const availableMaterials = computed(() => uniq(all.map(s => s.material)))

// dukungan laminasi bergantung material
const selectedSpec = computed(() => all.find(s => s.material === form.material) || null)
const laminationChoices = computed(() => {
  if (!selectedSpec.value) return ['none']
  return selectedSpec.value.supports_lamination ? ['none','doff','glossy'] : ['none']
})
const finishChoices = computed(() => props.options.finishes || ['none','straight_cut','kiss_cut','die_cut'])

// Konsistensi nilai terpilih
watch(() => form.material, (val) => {
  if (!availableMaterials.value.includes(val)) form.material = availableMaterials.value[0] ?? ''
  if (!laminationChoices.value.includes(form.lamination)) form.lamination = laminationChoices.value[0] ?? 'none'
})
watch(() => form.lamination, () => {
  if (!laminationChoices.value.includes(form.lamination)) form.lamination = laminationChoices.value[0] ?? 'none'
})
watch(() => form.finish, () => {
  if (!finishChoices.value.includes(form.finish)) form.finish = finishChoices.value[0] ?? 'none'
})

/* ========= Quote & add to cart (struktur sama POP) ========= */
const quoting = ref(false)
const quote = ref(null)
const errorMsg = ref('')

async function doQuote() {
  errorMsg.value = ''; quote.value = null; quoting.value = true
  try {
    const { data } = await axios.post(route('sticker.quote'), {
      product_slug: form.product_slug,
      material: form.material,
      lamination: form.lamination,
      finish: form.finish,
      qty: Number(form.qty || 1),
      note: note.value?.slice(0,300) || null,
    })
    quote.value = data
  } catch (e) {
    errorMsg.value = e?.response?.data?.message
      ?? (e?.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(', ') : 'Gagal menghitung harga.')
  } finally { quoting.value = false }
}

function stepDown() { form.qty = Math.max(minQty.value, Number(form.qty || 1) - stepQty.value) }
function stepUp()   { form.qty = Number(form.qty || 1) + stepQty.value }

async function addToCart() {
  if (!quote.value?.ok) return
  await axios.post(route('cart.add'), {
    product_type: 'sticker',
    product_id: quote.value.spec_snapshot.product_id,
    name: quote.value.spec_snapshot.product_name,
    qty: quote.value.qty,
    unit_price: quote.value.unit_price,
    spec_snapshot: quote.value.spec_snapshot,
    pricing_breakdown: quote.value.breakdown,
    note: note.value?.slice(0,500) || null,
  })
  router.visit(route('cart.index'))
}

// auto-quote (debounce)
let t=null
watch(form, () => { clearTimeout(t); t=setTimeout(doQuote,200) }, { deep:true })
doQuote()
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-semibold">{{ product.name }}</h1>
      <a href="/catalog/sticker" class="text-sm underline">← Kembali</a>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
      <!-- Gallery -->
      <div>
        <div class="aspect-[4/3] bg-white border rounded-xl overflow-hidden grid place-content-center cursor-zoom-in" @click="openLightbox(0)">
          <img v-if="mainImg" :src="mainImg" class="w-full h-full object-cover" alt="" />
          <div v-else class="text-gray-400">No Image</div>
        </div>
        <div v-if="images.length" class="flex gap-3 overflow-x-auto mt-3">
          <button
            v-for="(img,idx) in images" :key="idx"
            @click="openLightbox(idx)"
            :class="['w-20 h-16 border rounded-lg overflow-hidden',
                     idx===lightboxIndex ? 'ring-2 ring-blue-600' : '']">
            <img :src="img" class="w-full h-full object-cover" alt="" />
          </button>
        </div>

        <!-- Lightbox sederhana -->
        <div v-if="lightboxOpen" class="fixed inset-0 bg-black/80 z-50 grid place-items-center" @click.self="closeLightbox">
          <button class="absolute top-4 right-4 text-white text-2xl" @click="closeLightbox">✕</button>
          <button class="absolute left-4 text-white text-2xl" @click="prevImg">‹</button>
          <img :src="images[lightboxIndex]" class="max-h-[85vh] max-w-[90vw] object-contain" />
          <button class="absolute right-4 text-white text-2xl" @click="nextImg">›</button>
        </div>
      </div>

      <!-- Config & Quote -->
      <div class="space-y-4">
        <div class="border rounded-xl bg-white p-4">
          <div class="grid sm:grid-cols-2 gap-4">
            <!-- Material -->
            <div>
              <label class="block text-sm text-gray-600 mb-1">Material</label>
              <select v-model="form.material" class="w-full border rounded-md px-3 py-2">
                <option v-for="m in availableMaterials" :key="m" :value="m">{{ m }}</option>
              </select>
            </div>

            <!-- Laminasi -->
            <div>
              <label class="block text-sm text-gray-600 mb-1">Laminasi</label>
              <select v-model="form.lamination" class="w-full border rounded-md px-3 py-2" :disabled="!selectedSpec?.supports_lamination">
                <option v-for="l in laminationChoices" :key="l" :value="l">{{ l }}</option>
              </select>
              <p v-if="selectedSpec && !selectedSpec.supports_lamination" class="text-xs text-gray-500 mt-1">Material ini tidak mendukung laminasi.</p>
            </div>

            <!-- Finishing -->
            <div class="sm:col-span-2">
              <label class="block text-sm text-gray-600 mb-1">Finishing</label>
              <div class="grid grid-cols-2 gap-2">
                <label class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer">
                  <input type="radio" value="none" v-model="form.finish"><span>Tanpa Finishing</span>
                </label>
                <label class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer">
                  <input type="radio" value="straight_cut" v-model="form.finish"><span>Potong Lurus</span>
                </label>
                <label class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer">
                  <input type="radio" value="kiss_cut" v-model="form.finish"><span>Kiss-cut</span>
                </label>
                <label class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer">
                  <input type="radio" value="die_cut" v-model="form.finish"><span>Die-cut</span>
                </label>
              </div>
            </div>

            <!-- Qty -->
            <div class="sm:col-span-2">
              <label class="block text-sm text-gray-600 mb-1">Jumlah (lembar)</label>
              <div class="flex items-center gap-2">
                <button class="px-3 py-2 border rounded-md" @click="stepDown">-{{ stepQty }}</button>
                <input v-model.number="form.qty" type="number" :min="minQty" class="w-32 text-center border rounded-md px-3 py-2" />
                <button class="px-3 py-2 border rounded-md" @click="stepUp">+{{ stepQty }}</button>
              </div>
              <p class="text-xs text-gray-500 mt-1">Minimal {{ minQty }} lembar, kelipatan {{ stepQty }}.</p>
            </div>

            <!-- Catatan -->
            <div class="sm:col-span-2">
              <label class="block text-sm text-gray-600 mb-1">Catatan (opsional)</label>
              <input v-model="note" type="text" class="w-full border rounded-md px-3 py-2" placeholder="Contoh: file siap cetak, tanpa bleed" />
            </div>
          </div>

          <!-- Error -->
          <div v-if="errorMsg" class="mt-3 text-sm text-rose-600">{{ errorMsg }}</div>
        </div>

        <!-- Quote -->
        <div class="border rounded-xl bg-white p-4">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm text-gray-600">Harga / lembar</div>
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

          <div v-if="quote?.ok" class="grid sm:grid-cols-3 gap-3 mt-4 text-sm">
            <div class="p-2 bg-gray-50 rounded-md">
              <div class="text-gray-500">Base</div>
              <div class="font-semibold">Rp {{ fmt(quote.breakdown.base) }}</div>
            </div>
            <div class="p-2 bg-gray-50 rounded-md">
              <div class="text-gray-500">Laminasi</div>
              <div class="font-semibold">Rp {{ fmt(quote.breakdown.lamination_fee) }}</div>
            </div>
            <div class="p-2 bg-gray-50 rounded-md">
              <div class="text-gray-500">Finishing</div>
              <div class="font-semibold">Rp {{ fmt(quote.breakdown.finish_fee) }}</div>
            </div>
          </div>

          <div class="mt-4 flex items-center justify-end gap-2">
            <button class="px-4 py-2 rounded-md border hover:bg-gray-50" @click="doQuote">Hitung Ulang</button>
            <button
              class="px-4 py-2 rounded-md bg-blue-600 text-white disabled:opacity-60"
              :disabled="!quote?.ok || quoting"
              @click="addToCart"
            >
              Tambah ke Keranjang
            </button>
          </div>

          <p class="text-xs text-gray-500 mt-2">
            * Harga belum termasuk penyesuaian minimum order di checkout (jika total &lt; Rp 15.000).
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
