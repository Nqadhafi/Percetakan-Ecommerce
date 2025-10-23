<script setup>
import { reactive, ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { router, usePage } from '@inertiajs/vue3'
const page = usePage()
const isLoggedIn = computed(() => !!page.props?.auth?.user)
const props = defineProps({
  product: { type: Object, required: true }, // {id,name,slug,thumbnail_url,gallery:[]}
  options: { type: Object, required: true },
  // specs TANPA size: [{material, side, lamination, cutting}, ...]
  specs:   { type: Array,  required: true },
})

const note = ref('')
const fmt = (n) => new Intl.NumberFormat('id-ID').format(n ?? 0)

/* ========= Gambar: thumbnail → gallery ========= */
const images = computed(() => {
  const arr = []
  if (props.product?.thumbnail_url) arr.push(props.product.thumbnail_url)
  const gal = Array.isArray(props.product?.gallery) ? props.product.gallery : []
  for (const g of gal) { if (g && !arr.includes(g)) arr.push(g) }
  return arr
})
const mainImg = computed(() => images.value[0] || null) // thumbnail prioritas
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

/* ========= Form & filter opsi (tanpa size) ========= */
const form = reactive({
  product_slug: props.product.slug,
  material: props.options.materials?.[0] ?? '',
  side: props.options.sides?.[0] ?? '1s',
  lamination: props.options.laminations?.[0] ?? 'none',
  cutting: props.options.cuttings?.[0] ?? 'tanpa_potong',
  qty: props.options.min_qty || 1,
})

const minQty  = computed(() => props.options.min_qty  || 1)
const stepQty = computed(() => props.options.step_qty || 50)

const all = props.specs
const uniq = (arr) => Array.from(new Set(arr))

const availableMaterials = computed(() => uniq(all.map(s => s.material)))
const availableSides = computed(() => uniq(all.filter(s => s.material === form.material).map(s => s.side)))
const availableLaminations = computed(() =>
  uniq(all.filter(s => s.material === form.material && s.side === form.side).map(s => s.lamination))
)
const availableCuttings = computed(() =>
  uniq(all.filter(s =>
    s.material === form.material &&
    s.side === form.side &&
    s.lamination === form.lamination
  ).map(s => s.cutting))
)

// Konsistensi nilai terpilih
watch(() => form.material, (val) => {
  if (!availableMaterials.value.includes(val)) form.material = availableMaterials.value[0] ?? ''
  if (!availableSides.value.includes(form.side)) form.side = availableSides.value[0] ?? '1s'
  if (!availableLaminations.value.includes(form.lamination)) form.lamination = availableLaminations.value[0] ?? 'none'
  if (!availableCuttings.value.includes(form.cutting)) form.cutting = availableCuttings.value[0] ?? 'tanpa_potong'
})
watch(() => form.side, () => {
  if (!availableSides.value.includes(form.side)) form.side = availableSides.value[0] ?? '1s'
  if (!availableLaminations.value.includes(form.lamination)) form.lamination = availableLaminations.value[0] ?? 'none'
  if (!availableCuttings.value.includes(form.cutting)) form.cutting = availableCuttings.value[0] ?? 'tanpa_potong'
})
watch(() => form.lamination, () => {
  if (!availableLaminations.value.includes(form.lamination)) form.lamination = availableLaminations.value[0] ?? 'none'
  if (!availableCuttings.value.includes(form.cutting)) form.cutting = availableCuttings.value[0] ?? 'tanpa_potong'
})

/* ========= Quote & add to cart ========= */
const quoting = ref(false)
const quote = ref(null)
const errorMsg = ref('')

async function doQuote() {
  errorMsg.value = ''; quote.value = null; quoting.value = true
  try {
    const { data } = await axios.post(route('pop.quote'), form)
    quote.value = data
  } catch (e) {
    errorMsg.value = e?.response?.data?.message
      ?? (e?.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(', ') : 'Gagal menghitung harga.')
  } finally { quoting.value = false }
}

function stepDown() { form.qty = Math.max(minQty.value, form.qty - stepQty.value) }
function stepUp()   { form.qty = form.qty + stepQty.value }

async function addToCart() {
  if (!quote.value?.ok) return
  await axios.post(route('cart.add'), {
    product_type: 'pop',
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
  <div class="grid lg:grid-cols-3 gap-6">
    <!-- LEFT: preview + thumbs -->
    <div class="lg:col-span-1">
      <div class="border rounded-xl bg-white p-4">
        <!-- Main preview (thumbnail prioritas) -->
        <button
          type="button"
          class="h-56 w-full rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center group relative"
          @click="openLightbox(0)"
        >
          <img
            v-if="mainImg"
            :src="mainImg"
            alt="preview"
            class="w-full h-full object-cover"
          >
          <div v-else class="text-gray-500">Preview</div>
          <div
            v-if="images.length"
            class="absolute bottom-2 right-2 bg-black/50 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition"
          >
            Lihat gambar ({{ images.length }})
          </div>
        </button>

        <!-- Thumbs (jika >1) -->
        <div v-if="images.length > 1" class="flex gap-2 mt-3">
          <button
            v-for="(src,i) in images"
            :key="i"
            type="button"
            class="w-14 h-14 rounded-md overflow-hidden border focus:ring-2 ring-blue-500"
            @click="openLightbox(i)"
          >
            <img :src="src" class="w-full h-full object-cover" alt="">
          </button>
        </div>
      </div>
    </div>

    <!-- MIDDLE: form spesifikasi -->
    <div class="lg:col-span-1">
      <div class="border rounded-xl bg-white p-4 space-y-4">
        <h1 class="text-lg font-semibold">{{ product.name }}</h1>

        <div>
          <label class="block text-sm text-gray-600 mb-1">Material</label>
          <select v-model="form.material" class="w-full border rounded-md px-3 py-2">
            <option v-for="m in availableMaterials" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Sisi Cetak</label>
            <select v-model="form.side" class="w-full border rounded-md px-3 py-2">
              <option v-for="s in availableSides" :key="s" :value="s">{{ s }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Laminasi</label>
            <select v-model="form.lamination" class="w-full border rounded-md px-3 py-2">
              <option v-for="l in availableLaminations" :key="l" :value="l">{{ l }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">Finishing Potong</label>
          <select v-model="form.cutting" class="w-full border rounded-md px-3 py-2">
            <option v-for="c in availableCuttings" :key="c" :value="c">{{ c.replaceAll('_',' ') }}</option>
          </select>
        </div>

        <!-- Qty -->
        <div>
          <label class="block text-sm text-gray-600 mb-1">Jumlah (kelipatan {{ stepQty }})</label>
          <div class="inline-flex border rounded-md overflow-hidden">
            <button class="px-3 py-2" @click="stepDown">−</button>
            <input class="w-24 text-center border-l border-r" v-model.number="form.qty" type="number" :min="minQty" :step="stepQty">
            <button class="px-3 py-2" @click="stepUp">+</button>
          </div>
          <div class="text-xs text-gray-500 mt-1">Minimal {{ minQty }}.</div>
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">Catatan (opsional)</label>
          <textarea v-model="note" rows="2" class="w-full border rounded-md px-3 py-2" placeholder="Contoh: potong jadi 2 bagian, kirim file via WA..."></textarea>
          <div class="text-xs text-gray-400 mt-1">Maks 500 karakter.</div>
        </div>
      </div>
    </div>

    <!-- RIGHT: harga & add to cart -->
    <div class="lg:col-span-1">
      <div class="border rounded-xl bg-white p-4 sticky top-24">
        <div class="text-sm text-gray-500">Harga</div>

        <div v-if="errorMsg" class="mt-2 text-sm text-red-600">
          {{ errorMsg }}
        </div>

        <div v-else class="mt-2">
          <div v-if="quoting" class="text-gray-600">Menghitung…</div>
          <div v-else-if="quote?.ok">
            <div class="text-2xl font-semibold">
              Rp {{ fmt(quote.total) }}
            </div>
            <div class="text-sm text-gray-500 mt-1">
              (Rp {{ fmt(quote.unit_price) }} x {{ quote.qty }})
            </div>

            <div class="mt-3 border rounded-md p-3 bg-gray-50">
              <div class="flex justify-between text-sm">
                <span>Base</span>
                <span>Rp {{ fmt(quote.breakdown?.base) }}</span>
              </div>
              <div class="flex justify-between text-sm mt-1">
                <span>Biaya potong</span>
                <span>Rp {{ fmt(quote.breakdown?.cutting_fee) }}</span>
              </div>
            </div>

<button
  v-if="isLoggedIn"
  class="mt-4 w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700"
  @click="addToCart"
>
  Tambah ke Keranjang
</button>

<!-- Jika belum login: arahkan ke login -->
<a
  v-else
  class="mt-4 w-full inline-flex items-center justify-center bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700"
  :href="route('login')"
>
  Masuk untuk Menambahkan
</a>
          </div>
        </div>

        <div class="mt-4 text-xs text-gray-500">
          Harga final akan dikunci di keranjang.
        </div>
      </div>
    </div>

    <!-- LIGHTBOX / MODAL GALLERY -->
    <teleport to="body">
      <div
        v-if="lightboxOpen"
        class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center"
        @click.self="closeLightbox"
      >
        <button
          class="absolute top-4 right-4 text-white/80 hover:text-white text-2xl"
          @click="closeLightbox"
          aria-label="Tutup"
        >✕</button>

        <button
          class="absolute left-2 md:left-4 text-white/80 hover:text-white text-3xl px-3 py-2"
          @click.stop="prevImg"
          aria-label="Sebelumnya"
        >‹</button>

        <img
          :src="images[lightboxIndex]"
          class="max-h-[88vh] max-w-[92vw] object-contain rounded-lg shadow-lg"
          alt="gallery"
        />

        <button
          class="absolute right-2 md:right-4 text-white/80 hover:text-white text-3xl px-3 py-2"
          @click.stop="nextImg"
          aria-label="Berikutnya"
        >›</button>

        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/80 text-sm">
          {{ lightboxIndex + 1 }} / {{ images.length }}
        </div>
      </div>
    </teleport>
  </div>
</template>
