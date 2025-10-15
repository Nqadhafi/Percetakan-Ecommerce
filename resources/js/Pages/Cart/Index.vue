<script setup>
import { ref, computed } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const cart = ref(page.props.cart) // { id, items:[], subtotal, grand_total, ... }
const items = computed(() => cart.value?.items ?? [])

const fmt = (n) => new Intl.NumberFormat('id-ID').format(n ?? 0)

async function updateQty(item, newQty) {
  if (newQty < 1) return
  try {
    await axios.post(route('cart.update', { item: item.id }), { qty: newQty })
    // reload data cart supaya totals & badge ter-update
    router.visit(route('cart.index'), { only: ['cart','cartCount'], preserveScroll: true })
  } catch (e) {
    alert('Gagal mengubah jumlah item.')
  }
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
             <button class="px-3 py-1" @click="updateQty(item, item.qty - 1)">−</button>
             <input class="w-14 text-center border-l border-r" :value="item.qty"
                    @change="e => updateQty(item, parseInt(e.target.value||item.qty))">
             <button class="px-3 py-1" @click="updateQty(item, item.qty + 1)">+</button>
           </div>
         </template>
         <template v-else>
           <span class="text-sm text-gray-500">Qty: 1 (meteran)</span>
         </template>
         <button class="text-red-600 text-sm hover:underline" @click="removeItem(item)">Hapus</button>
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
        <button class="mt-4 w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 disabled:opacity-60"
                :disabled="items.length===0">
          Checkout (coming soon)
        </button>
        <p class="text-xs text-gray-500 mt-2">Checkout akan kita aktifkan setelah modul order siap.</p>
      </div>
    </div>
  </div>
</template>
