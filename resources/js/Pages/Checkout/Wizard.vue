<script setup>
import { reactive, ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  cart: { type: Object, required: true }, // { items:[], subtotal }
  profile_prefill: { type: Object, required: true }, // {name,phone,address}
  config: { type: Object, required: true }, // { min_order_total: 15000 }
})

const step = ref(1) // 1=Data, 2=Review, 3=Pembayaran
const loading = ref(false)
const errors = reactive({}) // validation bag

// STEP 1 — Data pelanggan
const formCustomer = reactive({
  customer_name: props.profile_prefill?.name ?? '',
  customer_phone: props.profile_prefill?.phone ?? '',
  customer_address: props.profile_prefill?.address ?? '',
})

// STEP 3 — Pembayaran
const formPay = reactive({
  payment_method: 'bank', // 'bank' | 'qris'
  payment_note: '',
  payment_proof: null, // File
})
function onPickFile(e) { formPay.payment_proof = e.target.files?.[0] ?? null }

// Ringkasan cart
const subtotal = computed(() => Number(props.cart?.subtotal || 0))
const minOrder  = computed(() => Number(props.config?.min_order_total || 0))
const adjustment = computed(() => {
  if (subtotal.value > 0 && subtotal.value < minOrder.value) {
    return minOrder.value - subtotal.value
  }
  return 0
})
const total = computed(() => subtotal.value + adjustment.value)

// Helpers
function fmt(n){ return new Intl.NumberFormat('id-ID').format(n ?? 0) }
function go(n){ 
  for (const k in errors) delete errors[k]
  step.value = n }

// Validasi simpel di FE untuk UX (BE tetap sumber kebenaran)
function validateStep1(){
  errors.customer_name = !formCustomer.customer_name ? 'Nama wajib diisi.' : ''
  errors.customer_phone = !formCustomer.customer_phone ? 'Nomor WA wajib diisi.' : ''
  errors.customer_address = !formCustomer.customer_address ? 'Alamat wajib diisi.' : ''
  return !(errors.customer_name || errors.customer_phone || errors.customer_address)
}
function validateStep3(){
  errors.payment_method = !formPay.payment_method ? 'Pilih metode pembayaran.' : ''
  // bukti optional; tapi jika user pilih upload, batasi ukuran di FE juga (<=5MB)
  if (formPay.payment_proof && formPay.payment_proof.size > 5 * 1024 * 1024) {
    errors.payment_proof = 'Ukuran bukti maksimal 5MB.'
  } else {
    errors.payment_proof = ''
  }
  return !(errors.payment_method || errors.payment_proof)
}

// Submit ke backend
async function submitOrder(){
  if (!validateStep1() || !validateStep3()) { step.value = !validateStep1() ? 1 : 3; return }
  if (loading.value) return
  loading.value = true
  // pakai FormData supaya file terkirim
  const fd = new FormData()
  fd.append('customer_name', formCustomer.customer_name)
  fd.append('customer_phone', formCustomer.customer_phone)
  fd.append('customer_address', formCustomer.customer_address)
  fd.append('payment_method', formPay.payment_method)
  if (formPay.payment_note) fd.append('payment_note', formPay.payment_note)
  if (formPay.payment_proof) fd.append('payment_proof', formPay.payment_proof)
  fd.append('meta[source]', 'web')
  fd.append('meta[flow]', 'checkout_wizard_v1')
  fd.append('meta[applied_min_adjustment]', String(adjustment.value))

  // gunakan Inertia router.post agar auto-follow redirect ke success
  router.post(route('checkout.submit'), fd, {
    forceFormData: true,
    onError: (errs) => {
      // map error bag
      Object.assign(errors, errs || {})
      // arahkan user ke step relevan
      if (errs.customer_name || errs.customer_phone || errs.customer_address) step.value = 1
      else step.value = 3
      window.scrollTo({ top: 0, behavior: 'smooth' })
    },
    onFinish: () => { loading.value = false },
  })
}
</script>

<template>
  <div class="max-w-5xl mx-auto py-8 px-4">
    <!-- Stepper -->
    <div class="flex items-center justify-center gap-6 mb-8">
      <div class="flex items-center gap-2">
        <div :class="['w-8 h-8 rounded-full grid place-content-center text-sm',
          step>=1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600']">1</div>
        <span class="text-sm" :class="step>=1?'text-blue-600':'text-gray-500'">Data Pelanggan</span>
      </div>
      <div class="h-px w-10 bg-gray-300"></div>
      <div class="flex items-center gap-2">
        <div :class="['w-8 h-8 rounded-full grid place-content-center text-sm',
          step>=2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600']">2</div>
        <span class="text-sm" :class="step>=2?'text-blue-600':'text-gray-500'">Rincian Pesanan</span>
      </div>
      <div class="h-px w-10 bg-gray-300"></div>
      <div class="flex items-center gap-2">
        <div :class="['w-8 h-8 rounded-full grid place-content-center text-sm',
          step>=3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600']">3</div>
        <span class="text-sm" :class="step>=3?'text-blue-600':'text-gray-500'">Pembayaran</span>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <!-- LEFT: Konten step -->
      <div class="lg:col-span-2 space-y-6">
        <!-- STEP 1 -->
        <div v-show="step===1" class="border rounded-xl bg-white p-4 space-y-4">
          <h2 class="text-lg font-semibold">Data Pelanggan</h2>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Nama Lengkap</label>
            <input v-model="formCustomer.customer_name" type="text" class="w-full border rounded-md px-3 py-2" />
            <p v-if="errors.customer_name" class="text-sm text-red-600 mt-1">{{ errors.customer_name }}</p>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">No. WhatsApp</label>
            <input v-model="formCustomer.customer_phone" type="text" class="w-full border rounded-md px-3 py-2" placeholder="08xxxxxxxxxx" />
            <p v-if="errors.customer_phone" class="text-sm text-red-600 mt-1">{{ errors.customer_phone }}</p>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Alamat Pengiriman</label>
            <textarea v-model="formCustomer.customer_address" rows="3" class="w-full border rounded-md px-3 py-2"></textarea>
            <p v-if="errors.customer_address" class="text-sm text-red-600 mt-1">{{ errors.customer_address }}</p>
          </div>

          <div class="flex justify-end gap-3">
            <button class="px-4 py-2 rounded-md border" @click="$inertia.visit(route('cart.index'))">Kembali ke Keranjang</button>
            <button class="px-4 py-2 rounded-md bg-blue-600 text-white" @click="validateStep1() && go(2)">Lanjut</button>
          </div>
        </div>

        <!-- STEP 2 -->
        <div v-show="step===2" class="border rounded-xl bg-white p-4">
          <h2 class="text-lg font-semibold mb-3">Rincian Pesanan</h2>

          <div class="divide-y">
            <div v-for="(it,idx) in cart.items" :key="idx" class="py-3 flex items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="font-medium">{{ it.name }}</div>
                <div class="text-sm text-gray-600">
                  <!-- ringkas spes -->
                  <template v-if="it.product_type==='mmt'">
                    <span v-if="it.spec_snapshot?.width_m && it.spec_snapshot?.height_m">
                      {{ it.spec_snapshot.width_m }}×{{ it.spec_snapshot.height_m }} m
                    </span>
                    <span v-if="it.spec_snapshot?.material"> • {{ it.spec_snapshot.material }}</span>
                    <span v-if="Array.isArray(it.spec_snapshot?.finishing) && it.spec_snapshot.finishing.length"> • finishing: {{ it.spec_snapshot.finishing.map(f => f?.name || f).join(', ') }} </span>
                  </template>
                  <template v-else>
                    <span v-if="it.spec_snapshot?.material">{{ it.spec_snapshot.material }}</span>
                    <span v-if="it.spec_snapshot?.side"> • {{ it.spec_snapshot.side }}</span>
                    <span v-if="it.spec_snapshot?.lamination && it.spec_snapshot.lamination!=='none'"> • lam: {{ it.spec_snapshot.lamination }}</span>
                    <span v-if="it.spec_snapshot?.cutting"> • cut: {{ (it.spec_snapshot.cutting||'').replaceAll('_',' ') }}</span>
                  </template>
                  <span v-if="it.note"> • catatan: {{ it.note }}</span>
                </div>
                <div class="text-xs text-gray-500 mt-1">Qty: {{ it.qty }}</div>
              </div>
              <div class="text-right shrink-0">
                <div class="text-sm text-gray-500">Harga</div>
                <div class="font-semibold">Rp {{ fmt(it.unit_price) }}</div>
                <div class="text-sm text-gray-500 mt-1">Subtotal</div>
                <div class="font-semibold">Rp {{ fmt(it.total_price) }}</div>
              </div>
            </div>
          </div>

          <div class="mt-4 flex justify-between text-sm">
            <span>Subtotal</span><span class="font-semibold">Rp {{ fmt(subtotal) }}</span>
          </div>
          <div v-if="adjustment>0" class="flex justify-between text-sm mt-1">
            <span>Penyesuaian Minimum Order</span><span class="font-semibold">Rp {{ fmt(adjustment) }}</span>
          </div>
          <div class="flex justify-between text-base mt-2 border-t pt-2">
            <span class="font-semibold">Grand Total</span><span class="font-semibold">Rp {{ fmt(total) }}</span>
          </div>

          <div class="mt-4 flex justify-between">
            <button class="px-4 py-2 rounded-md border" @click="go(1)">← Kembali</button>
            <button class="px-4 py-2 rounded-md bg-blue-600 text-white" @click="go(3)">Lanjut ke Pembayaran →</button>
          </div>
        </div>

        <!-- STEP 3 -->
        <div v-show="step===3" class="border rounded-xl bg-white p-4 space-y-4">
          <h2 class="text-lg font-semibold">Pembayaran</h2>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Metode</label>
            <div class="flex items-center gap-6">
              <label class="flex items-center gap-2">
                <input type="radio" value="bank" v-model="formPay.payment_method">
                <span>Transfer Bank</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" value="qris" v-model="formPay.payment_method">
                <span>QRIS</span>
              </label>
            </div>
            <p v-if="errors.payment_method" class="text-sm text-red-600 mt-1">{{ errors.payment_method }}</p>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Bukti Pembayaran (opsional)</label>
            <input type="file" accept="image/*,application/pdf" @change="onPickFile" />
            <p class="text-xs text-gray-500 mt-1">Format: JPG/PNG/WebP/PDF, maks 5MB.</p>
            <p v-if="errors.payment_proof" class="text-sm text-red-600 mt-1">{{ errors.payment_proof }}</p>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Catatan Pembayaran (opsional)</label>
            <textarea v-model="formPay.payment_note" rows="2" class="w-full border rounded-md px-3 py-2" placeholder="Contoh: Transfer BCA jam 10:30 a/n ..."></textarea>
          </div>

          <div class="flex justify-between">
            <button class="px-4 py-2 rounded-md border" @click="go(2)">← Kembali</button>
            <button
              class="px-4 py-2 rounded-md bg-blue-600 text-white disabled:opacity-60"
              :disabled="loading"
              @click="submitOrder"
            >
              {{ loading ? 'Mengirim...' : 'Kirim Pesanan' }}
            </button>
          </div>
        </div>
      </div>

      <!-- RIGHT: Ringkasan -->
      <div class="lg:col-span-1">
        <div class="border rounded-xl bg-white p-4 sticky top-24">
          <div class="text-sm text-gray-500">Ringkasan</div>
          <div class="mt-2 flex justify-between text-sm">
            <span>Subtotal</span><span>Rp {{ fmt(subtotal) }}</span>
          </div>
          <div v-if="adjustment>0" class="flex justify-between text-sm mt-1">
            <span>Penyesuaian Minimum Order</span><span>Rp {{ fmt(adjustment) }}</span>
          </div>
          <div class="flex justify-between text-base mt-2 border-t pt-2">
            <span class="font-semibold">Total</span><span class="font-semibold">Rp {{ fmt(total) }}</span>
          </div>

          <div class="mt-4 text-xs text-gray-500">
            * Minimum order Rp {{ fmt(minOrder) }} akan diterapkan pada saat submit.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
