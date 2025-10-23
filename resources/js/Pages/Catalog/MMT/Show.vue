<script setup>
import { reactive, ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { router, usePage } from '@inertiajs/vue3'
const page = usePage()
const isLoggedIn = computed(() => !!page.props?.auth?.user)
const props = defineProps({
  product: { type: Object, required: true },      // {id,name,slug,kind, thumbnail_url, gallery:[] ...}
  // Meteran:
  materials: { type: [Array, null], default: null },     // [{material, base_price_per_m2}]
  finishings: { type: [Array, null], default: null },    // [{finishing, price_type, price_value}]
  min_area: { type: [Number, null], default: null },
  width_extra_m: { type: [Number, null], default: null },
  // Paket:
  sizesByMaterial: { type: [Object, null], default: null }, // { MATERIAL: [size,size] }
})

const fmt = (n) => new Intl.NumberFormat('id-ID').format(n ?? 0)

/* =========================
   Gambar: thumbnail → gallery
   ========================= */
const images = computed(() => {
  const arr = []
  if (props.product?.thumbnail_url) arr.push(props.product.thumbnail_url)
  const gal = Array.isArray(props.product?.gallery) ? props.product.gallery : []
  for (const g of gal) {
    if (g && !arr.includes(g)) arr.push(g)
  }
  return arr
})
const mainImg = computed(() => images.value[0] || null) // UTAMA: thumbnail dulu; kalau tidak ada, ambil pertama dari galeri
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

/* =========================
   Meteran state
   ========================= */
const formM = reactive({
  product_slug: props.product.slug,
  width: 200, height: 100, unit: 'cm',
  material: props.materials?.[0]?.material ?? '',
})
const finDefs = computed(() => props.finishings || [])
const finState = reactive({})
if (finDefs.value) for (const f of finDefs.value) finState[f.finishing] = { checked: false, qty: 1 }
const areaPreview = computed(() => {
  let w = Number(formM.width) || 0, h = Number(formM.height) || 0
  if (formM.unit === 'cm') { w/=100; h/=100 }
  const effW = w + (props.width_extra_m ?? 0)
  const area = Math.max(props.min_area || 1, +(h * effW).toFixed(4))
  return { w, h, effW, area }
})

/* =========================
   Paket state
   ========================= */
const matList = computed(() =>
  Array.isArray(props.materials) ? props.materials.map(m => m.material) : Object.keys(props.sizesByMaterial || {})
)
const firstMat = matList.value?.[0] ?? ''
const pack = reactive({
  product_slug: props.product.slug,
  material: firstMat,
  size: (props.sizesByMaterial && firstMat) ? (props.sizesByMaterial[firstMat]?.[0] ?? '') : '',
  qty: 1,
  include_pole: !!props.product.pole_default_include,
})
watch(() => pack.material, (m) => { pack.size = props.sizesByMaterial?.[m]?.[0] ?? '' })

/* =========================
   Quote shared
   ========================= */
const quoting = ref(false)
const quote = ref(null)
const errorMsg = ref('')
const note = ref('')

function buildFinishingPayload() {
  if (!finDefs.value) return []
  const list = []
  for (const f of finDefs.value) {
    const st = finState[f.finishing]
    if (!st?.checked) continue
    if (f.price_type === 'per_point') list.push({ name: f.finishing, qty: Math.max(1, parseInt(st.qty||1)) })
    else list.push({ name: f.finishing })
  }
  return list
}

async function doQuote() {
  errorMsg.value = ''; quote.value = null; quoting.value = true
  try {
    let payload
    if (props.product.kind === 'meteran') {
      payload = {
        product_slug: formM.product_slug,
        width: Number(formM.width),
        height: Number(formM.height),
        unit: formM.unit,
        material: formM.material,
        finishing: buildFinishingPayload(),
      }
    } else {
      payload = {
        product_slug: pack.product_slug,
        material: pack.material,
        size: pack.size,
        qty: pack.qty,
        include_pole: pack.include_pole,
      }
    }
    const { data } = await axios.post(route('mmt.quote'), payload)
    quote.value = data
  } catch (e) {
    errorMsg.value = e?.response?.data?.message
      ?? (e?.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(', ') : 'Gagal menghitung harga.')
  } finally { quoting.value = false }
}

async function addToCart() {
  if (!quote.value?.ok) return
  await axios.post(route('cart.add'), {
    product_type: 'mmt',
    product_id: quote.value.spec_snapshot.product_id,
    name: quote.value.spec_snapshot.product_name,
    qty: quote.value.qty, // meteran=1, paket=qty user
    unit_price: (props.product.kind === 'meteran' ? quote.value.total : quote.value.unit_price),
    spec_snapshot: quote.value.spec_snapshot,
    pricing_breakdown: quote.value.breakdown,
    note: note.value?.slice(0,500) || null,
  })
  router.visit(route('cart.index'))
}

// auto-quote
let t=null
watch([formM], ()=>{ if(props.product.kind==='meteran'){clearTimeout(t); t=setTimeout(doQuote,250)} }, {deep:true})
watch([finState],()=>{ if(props.product.kind==='meteran'){clearTimeout(t); t=setTimeout(doQuote,250)} }, {deep:true})
watch([pack],   ()=>{ if(props.product.kind==='package'){clearTimeout(t); t=setTimeout(doQuote,250)} }, {deep:true})
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

        <div v-if="product.kind==='meteran'" class="mt-3 text-sm text-gray-600">
          Luas: <b>{{ areaPreview.area.toFixed(2) }} m²</b>
          <div class="text-xs text-gray-500">
            Lebar efektif: {{ areaPreview.effW.toFixed(2) }} m
            <span v-if="(width_extra_m||0)>0"> (termasuk ekstra {{ width_extra_m }} m)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- MIDDLE: form -->
    <div class="lg:col-span-1">
      <div class="border rounded-xl bg-white p-4 space-y-4">
        <h1 class="text-lg font-semibold">{{ product.name }}</h1>

        <!-- Meteran form -->
        <template v-if="product.kind==='meteran'">
          <div class="grid grid-cols-[1fr_auto_1fr_auto_auto] gap-2 items-center">
            <div>
              <label class="block text-sm text-gray-600 mb-1">Lebar</label>
              <input v-model.number="formM.width" type="number" min="1" class="w-full border rounded-md px-3 py-2" />
            </div>
            <div class="text-center pt-6">×</div>
            <div>
              <label class="block text-sm text-gray-600 mb-1">Tinggi</label>
              <input v-model.number="formM.height" type="number" min="1" class="w-full border rounded-md px-3 py-2" />
            </div>
            <div class="text-center pt-6">in</div>
            <div>
              <label class="block text-sm text-gray-600 mb-1 invisible">Unit</label>
              <select v-model="formM.unit" class="border rounded-md px-3 py-2">
                <option value="cm">cm</option><option value="m">m</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Material</label>
            <select v-model="formM.material" class="w-full border rounded-md px-3 py-2">
              <option v-for="m in materials" :key="m.material" :value="m.material">
                {{ m.material }} (Rp {{ fmt(m.base_price_per_m2) }}/m²)
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-2">Finishing</label>
            <div class="space-y-2">
              <div v-for="f in finishings" :key="f.finishing" class="flex items-center justify-between border rounded-md px-3 py-2">
                <label class="flex items-center gap-2">
                  <input type="checkbox" v-model="finState[f.finishing].checked" />
                  <span class="text-sm">
                    {{ f.finishing }}
                    <span class="text-xs text-gray-500">
                      • {{ f.price_type === 'perimeter' ? 'per m keliling' : f.price_type === 'per_point' ? 'per titik' : 'flat' }}
                      • Rp {{ fmt(f.price_value) }}
                    </span>
                  </span>
                </label>
                <div v-if="f.price_type==='per_point' && finState[f.finishing].checked" class="flex items-center gap-2">
                  <span class="text-sm text-gray-600">Qty</span>
                  <input type="number" min="1" class="w-20 border rounded-md px-2 py-1" v-model.number="finState[f.finishing].qty" />
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- Paket form -->
        <template v-else>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Material</label>
            <select v-model="pack.material" class="w-full border rounded-md px-3 py-2">
              <option v-for="m in Object.keys(sizesByMaterial||{})" :key="m" :value="m">{{ m }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Ukuran</label>
            <select v-model="pack.size" class="w-full border rounded-md px-3 py-2">
              <option v-for="s in (sizesByMaterial?.[pack.material]||[])" :key="s" :value="s">{{ s }}</option>
            </select>
          </div>
          <div v-if="product.has_pole" class="flex items-center gap-2">
            <input id="incPole" type="checkbox" v-model="pack.include_pole" />
            <label for="incPole" class="text-sm text-gray-700">Sertakan Tiang ({{ product.pole_default_include ? 'default termasuk' : 'default tidak termasuk' }})</label>
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Jumlah</label>
            <input v-model.number="pack.qty" type="number" min="1" class="w-24 border rounded-md px-3 py-2" />
          </div>
        </template>

        <!-- Catatan -->
        <div>
          <label class="block text-sm text-gray-600 mb-1">Catatan (opsional)</label>
          <textarea v-model="note" rows="2" class="w-full border rounded-md px-3 py-2" placeholder="Contoh: 4 mata ayam di tiap pojok / kirim file via WA..."></textarea>
          <div class="text-xs text-gray-400 mt-1">Maks 500 karakter.</div>
        </div>
      </div>
    </div>

    <!-- RIGHT: harga & add to cart -->
    <div class="lg:col-span-1">
      <div class="border rounded-xl bg-white p-4 sticky top-24">
        <div class="text-sm text-gray-500">Harga</div>

        <div v-if="errorMsg" class="mt-2 text-sm text-red-600">{{ errorMsg }}</div>

        <div v-else class="mt-2">
          <div v-if="quoting" class="text-gray-600">Menghitung…</div>

          <div v-else-if="quote?.ok">
            <div class="text-2xl font-semibold">Rp {{ fmt(quote.total) }}</div>
            <div v-if="product.kind==='meteran'" class="text-sm text-gray-500 mt-1">
              (area {{ (quote.breakdown?.area_m2??0).toFixed ? quote.breakdown.area_m2.toFixed(2) : quote.breakdown.area_m2 }} m² × Rp {{ fmt(quote.breakdown?.material_price_per_m2) }})
            </div>
            <div v-else class="text-sm text-gray-500 mt-1">
              ({{ quote.breakdown?.material }} • {{ quote.breakdown?.size }} • {{ quote.qty }} pcs)
            </div>

            <div class="mt-3 border rounded-md p-3 bg-gray-50 space-y-1">
              <div class="flex justify-between text-sm" v-if="product.kind==='meteran'">
                <span>Base (material)</span><span>Rp {{ fmt(quote.breakdown?.base) }}</span>
              </div>
              <div v-if="product.kind==='meteran' && (quote.breakdown?.finishing_total ?? 0) > 0" class="text-sm text-gray-600">
                <div class="font-medium mt-1">Finishing</div>
                <ul class="list-disc ml-5">
                  <li v-for="(f,i) in quote.breakdown?.finishing" :key="i">
                    {{ f.name }} — {{ f.calc }} = Rp {{ fmt(f.total) }}
                  </li>
                </ul>
                <div class="flex justify-between text-sm mt-1">
                  <span>Subtotal finishing</span><span>Rp {{ fmt(quote.breakdown?.finishing_total) }}</span>
                </div>
              </div>
              <div v-if="product.kind==='package'" class="text-sm text-gray-600">
                <div>Include tiang: {{ quote.breakdown?.include_pole ? 'Ya' : 'Tidak' }}</div>
                <div v-if="product.pole_price">Harga tiang: Rp {{ fmt(product.pole_price) }}</div>
              </div>
            </div>

 <button
   v-if="isLoggedIn"
   class="mt-4 w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700"
   @click="addToCart"
 >
   Tambah ke Keranjang
 </button>


 <a
   v-else
   :href="route('login')"
   class="mt-4 w-full inline-flex items-center justify-center bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700"
 >
   Masuk untuk Menambahkan
 </a>
          </div>
        </div>

        <div class="mt-4 text-xs text-gray-500">
          Harga final dikunci di keranjang. Minimum order akan diterapkan saat checkout.
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
