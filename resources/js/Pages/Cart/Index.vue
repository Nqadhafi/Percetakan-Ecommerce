<script setup>
import { ref, computed } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const cart = ref(page.props.cart) // { id, items:[], subtotal, grand_total, ... }
const items = computed(() => cart.value?.items ?? [])

const fmt = (n) => new Intl.NumberFormat('id-ID').format(n ?? 0)
const itemNotes = ref({})
const itemQtys = ref({})
const showSuccess = ref(false)
const successMessage = ref('')
const isUpdating = ref(false)

const isLoggedIn = computed(() => !!page.props?.auth?.user)
const hasItems   = computed(() => (page.props?.cart?.items?.length ?? 0) > 0)

// Initialize notes and quantities from cart items
if (items.value) {
  items.value.forEach(item => {
    itemNotes.value[item.id] = item.note || ''
    itemQtys.value[item.id] = item.qty
  })
}

function showSuccessMessage(message) {
  successMessage.value = message
  showSuccess.value = true
  isUpdating.value = true
  
  // Use a longer timeout and make sure we're not interrupted
  setTimeout(() => {
    if (isUpdating.value) {
      showSuccess.value = false
      isUpdating.value = false
    }
  }, 2000)
}

async function updateCartItem(item) {
  const newQty = itemQtys.value[item.id]
  const newNote = itemNotes.value[item.id]

  if (newQty < 1) {
    alert('Jumlah minimal 1')
    itemQtys.value[item.id] = 1
    return
  }

  try {
    const updates = {
      qty: newQty,
      note: String(newNote ?? '').slice(0, 500)
    }
    
    // Show success message first
    showSuccessMessage('Item berhasil diperbarui')
    
    // Then update the data
    await axios.post(route('cart.update', { item: item.id }), updates)
    
    // Wait a bit before refreshing the page data
    setTimeout(() => {
      router.visit(route('cart.index'), { only: ['cart','cartCount'], preserveScroll: true })
    }, 1000)
  } catch (e) {
    isUpdating.value = false
    showSuccess.value = false
    alert('Gagal mengubah item.')
  }
}

// Functions to update local qty without saving
function incrementQty(item) {
  itemQtys.value[item.id] = (itemQtys.value[item.id] || 1) + 1
}

function decrementQty(item) {
  if (itemQtys.value[item.id] > 1) {
    itemQtys.value[item.id]--
  }
}

function updateLocalQty(item, newQty) {
  itemQtys.value[item.id] = Math.max(1, parseInt(newQty) || 1)
}
async function removeItem(item) {
  if (!confirm('Hapus item dari keranjang?')) return
  try {
    await axios.post(route('cart.remove', { item: item.id }))
    router.visit(route('cart.index'), { only: ['cart','cartCount'], preserveScroll: true })
  } catch (e) {
    alert('Gagal menghapus item.')
  }
}

function specSummary(spec) {
  // Tampilkan ringkas spesifikasi dari snapshot
  const parts = []
  if (spec?.size) parts.push(spec.size)
  if (spec?.material) parts.push(spec.material)
  if (spec?.width_m && spec?.height_m) parts.push(`${spec.width_m}×${spec.height_m} m`)
  if (spec?.area_m2) parts.push(`${spec.area_m2} m²`)
  if (spec?.side) parts.push(spec.side)
  if (spec?.lamination && spec.lamination !== 'none') parts.push(`lam: ${spec.lamination}`)
  if (spec?.finishing?.length) parts.push('finishing: ' + spec.finishing.map(f => f.name).join(', '))
  if (spec?.cutting) parts.push(`cut: ${spec.cutting.replaceAll('_',' ')}`)
  return parts.join(' • ')
}
</script>

<template>
  <!-- Sweet Alert Style Success Modal -->
  <div v-if="showSuccess" class="fixed inset-0 flex items-center justify-center z-50">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    
    <!-- Modal -->
    <div class="bg-white rounded-lg p-6 shadow-xl max-w-sm w-full mx-4 relative z-10 transform scale-100 transition-transform">
      <div class="text-center">
        <!-- Success Icon -->
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
          <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        
        <!-- Message -->
        <div class="text-lg font-medium text-gray-900 mb-2">Berhasil!</div>
        <div class="text-sm text-gray-600">{{ successMessage }}</div>
      </div>
    </div>
  </div>

  <div class="grid lg:grid-cols-3 gap-6">
    <!-- LIST ITEMS -->
    <div class="lg:col-span-2 space-y-4">
      <div v-if="items.length === 0" class="border rounded-xl bg-white p-8 text-center text-gray-600">
        Keranjang kosong. <Link href="/" class="text-blue-600 hover:underline">Belanja sekarang</Link>.
      </div>

      <div v-for="item in items" :key="item.id" class="border rounded-xl bg-white p-4 flex gap-4">
        <!-- Thumbnail placeholder -->
        <div class="w-20 h-20 bg-gray-100 rounded-md shrink-0"></div>

        <div class="flex-1 min-w-0">
          <div class="font-medium truncate">{{ item.name }}</div>
          <div class="text-sm text-gray-600 mt-1">
            {{ specSummary(item.spec_snapshot) }}
          </div>
          <div class="text-xs text-gray-500 mt-1">
            Tipe: {{ item.product_type.toUpperCase() }} • ID: #{{ item.product_id }}
          </div>

       <div class="mt-3 flex items-center gap-3">
         <template v-if="item.product_type !== 'mmt'">
           <!-- Qty stepper normal -->
           <div class="inline-flex border rounded-md overflow-hidden">
             <button class="px-3 py-1" @click="decrementQty(item)">−</button>
             <input class="w-14 text-center border-l border-r" 
                    :value="itemQtys[item.id]"
                    @change="e => updateLocalQty(item, e.target.value)">
             <button class="px-3 py-1" @click="incrementQty(item)">+</button>
           </div>
         </template>
         <template v-else>
           <span class="text-sm text-gray-500">Qty: 1 (meteran)</span>
         </template>
         <div class="mt-2">
  <label class="block text-xs text-gray-500 mb-1">Catatan</label>
  <textarea
    v-model="itemNotes[item.id]"
    rows="2"
    class="w-full border rounded-md px-2 py-1 text-sm"
    placeholder="Tambahkan catatan untuk pekerjaan ini (opsional)"
  ></textarea>
</div>
         <div class="flex items-center gap-3 mt-2">
           <button 
             class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
             @click="updateCartItem(item)"
           >
             Update
           </button>
           <button class="text-red-600 text-sm hover:underline" @click="removeItem(item)">Hapus</button>
         </div>
         
       </div>
        </div>

       <div class="text-right shrink-0">
         <div class="text-sm text-gray-500">
           {{ item.product_type === 'mmt' ? 'Harga' : 'Harga Satuan' }}
         </div>
         <div class="font-semibold">Rp {{ fmt(item.unit_price) }}</div>
         <div class="text-sm text-gray-500 mt-2">Subtotal</div>
         <div class="font-semibold">
           Rp {{ fmt(item.total_price ?? (item.unit_price * item.qty)) }}
         </div>
       </div>
      </div>
    </div>

    <!-- SUMMARY -->
    <div class="lg:col-span-1">
      <div class="border rounded-xl bg-white p-4 sticky top-24">
        <div class="flex justify-between text-sm text-gray-600">
          <span>Subtotal</span>
          <span>Rp {{ fmt(cart?.subtotal) }}</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600 mt-1">
          <span>Diskon</span>
          <span>- Rp {{ fmt(cart?.discount_total) }}</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600 mt-1">
          <span>Ongkir</span>
          <span>Rp {{ fmt(cart?.shipping_total) }}</span>
        </div>
        <div class="my-3 border-t"></div>
        <div class="flex justify-between text-base font-semibold">
          <span>Total</span>
          <span>Rp {{ fmt(cart?.grand_total) }}</span>
        </div>
     <!-- Jika login: tombol Checkout -->
     <button
       v-if="isLoggedIn"
       class="px-4 py-2 rounded-md bg-blue-600 text-white disabled:opacity-60"
       :disabled="!hasItems"
       @click="$inertia.visit(route('checkout.index'))"
     >
       Checkout
     </button>

     <!-- Jika belum login: ajak login dulu -->
     <a
       v-else
       class="px-4 py-2 rounded-md bg-blue-600 text-white"
       :href="route('login')"
     >
       Masuk untuk Checkout
     </a>
        <p class="text-xs text-gray-500 mt-2">Checkout akan kita aktifkan setelah modul order siap.</p>
      </div>
    </div>
  </div>
</template>
