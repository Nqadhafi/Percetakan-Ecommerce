<script setup>
import { reactive, ref, computed, watch } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  product: { type: Object, required: true },
  options: { type: Object, required: true },
  // specs sudah TANPA size: [{material, side, lamination, cutting}, ...]
  specs:   { type: Array,  required: true },
})

const fmt = (n) => new Intl.NumberFormat('id-ID').format(n ?? 0)

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

// ===== FILTER opsi (tanpa size) =====
const all = props.specs
const uniq = (arr) => Array.from(new Set(arr))

const availableMaterials = computed(() => uniq(all.map(s => s.material)))

const availableSides = computed(() =>
  uniq(all.filter(s => s.material === form.material).map(s => s.side))
)

const availableLaminations = computed(() =>
  uniq(all.filter(s =>
    s.material === form.material &&
    s.side === form.side
  ).map(s => s.lamination))
)

const availableCuttings = computed(() =>
  uniq(all.filter(s =>
    s.material === form.material &&
    s.side === form.side &&
    s.lamination === form.lamination
  ).map(s => s.cutting))
)

// ===== Konsistensi nilai terpilih =====
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

// ===== Quote & Add to cart =====
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
    <!-- LEFT: mock image -->
    <div class="lg:col-span-1">
      <div class="border rounded-xl bg-white p-4">
        <div class="h-56 bg-gray-100 rounded-lg"></div>
        <div class="mt-3 text-sm text-gray-500">Preview ilustrasi (opsional)</div>
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

        <!-- Qty tetap -->
        <div>
          <label class="block text-sm text-gray-600 mb-1">Jumlah (kelipatan {{ stepQty }})</label>
          <div class="inline-flex border rounded-md overflow-hidden">
            <button class="px-3 py-2" @click="stepDown">−</button>
            <input class="w-24 text-center border-l border-r" v-model.number="form.qty" type="number" :min="minQty" :step="stepQty">
            <button class="px-3 py-2" @click="stepUp">+</button>
          </div>
          <div class="text-xs text-gray-500 mt-1">Minimal {{ minQty }}.</div>
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
              class="mt-4 w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700"
              @click="addToCart"
            >
              Tambah ke Keranjang
            </button>
          </div>
        </div>

        <div class="mt-4 text-xs text-gray-500">
          Harga final akan dikunci di keranjang.
        </div>
      </div>
    </div>
  </div>
</template>
