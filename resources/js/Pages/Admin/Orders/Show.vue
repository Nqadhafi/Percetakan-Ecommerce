<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  order: { type: Object, required: true },
  items: { type: Array,  required: true },
  status_options: { type: Array, required: true },
})

const fmtMoney = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
const dateID = s => (s ? new Date(s).toLocaleString('id-ID') : '-')

const form = reactive({
  status: props.order.status,
})

function updateStatus() {
  router.patch(
    route('admin.orders.updateStatus', props.order.code),
    { status: form.status },
    {
      preserveScroll: true,
    }
  )
}

// helper badge style
function badgeClass(status) {
  return {
    'pending':        'bg-yellow-50 text-yellow-700 border-yellow-200',
    'paid':           'bg-emerald-50 text-emerald-700 border-emerald-200',
    'in_production':  'bg-blue-50 text-blue-700 border-blue-200',
    'completed':      'bg-green-50 text-green-700 border-green-200',
    'cancelled':      'bg-gray-200 text-gray-600 border-gray-300',
  }[status] || 'bg-gray-100 text-gray-600 border-gray-200'
}
</script>
<script>
// ⬇⬇⬇ penting: ini override layout bawaan app.js
export default {
  layout: AdminLayout,
}
</script>
<template #header>


      Pesanan / {{ order.code }}


    <div class="space-y-6">
      <!-- Top row: summary + status control -->
      <div class="grid lg:grid-cols-3 gap-4">
        <!-- Kiri: Ringkasan Pesanan -->
        <div class="lg:col-span-2 border rounded-xl bg-white p-4 space-y-3">
          <div class="flex items-start justify-between">
            <div>
              <div class="text-sm text-gray-500">Kode Order</div>
              <div class="font-semibold text-lg">{{ order.code }}</div>
            </div>
            <div class="text-right">
              <div class="text-sm text-gray-500">Tanggal</div>
              <div class="font-semibold">{{ dateID(order.created_at) }}</div>
            </div>
          </div>

          <div class="grid sm:grid-cols-3 gap-3 text-sm">
            <div class="p-3 bg-gray-50 rounded-lg">
              <div class="text-gray-500">Subtotal</div>
              <div class="font-semibold">Rp {{ fmtMoney(order.subtotal_amount) }}</div>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
              <div class="text-gray-500">Penyesuaian</div>
              <div class="font-semibold">Rp {{ fmtMoney(order.adjustment_amount) }}</div>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
              <div class="text-gray-500">Total Bayar</div>
              <div class="font-semibold text-blue-600 text-lg">
                Rp {{ fmtMoney(order.total_amount) }}
              </div>
            </div>
          </div>

          <div class="grid sm:grid-cols-3 gap-3 text-sm mt-2">
            <div class="p-3 border rounded-lg">
              <div class="text-gray-500">Pembayaran</div>
              <div class="font-semibold uppercase">
                {{ order.payment_method }}
              </div>
              <div v-if="order.payment_proof_url" class="text-xs mt-2">
                <a :href="order.payment_proof_url"
                   target="_blank"
                   class="text-blue-600 underline">
                  Lihat bukti bayar
                </a>
              </div>
            </div>

            <div class="p-3 border rounded-lg sm:col-span-2">
              <div class="text-gray-500">Catatan Pembayaran Customer</div>
              <div class="font-semibold break-words whitespace-pre-wrap">
                {{ order.payment_note || '—' }}
              </div>
            </div>
          </div>
        </div>

        <!-- Kanan: Status & Kontak -->
        <div class="border rounded-xl bg-white p-4 space-y-4">
          <div>
            <div class="text-sm text-gray-500">Status Pesanan</div>
            <div class="mt-1">
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border font-semibold"
                :class="badgeClass(order.status)"
              >
                {{ order.status }}
              </span>
            </div>
          </div>

          <div class="text-sm">
            <label class="block text-gray-600 mb-1 font-medium">Ubah Status</label>
            <select
              v-model="form.status"
              class="w-full border rounded-md px-3 py-2 text-sm"
            >
              <option
                v-for="opt in status_options"
                :key="opt"
                :value="opt"
              >
                {{ opt }}
              </option>
            </select>
            <button
              class="mt-2 w-full bg-blue-600 text-white text-sm font-semibold px-3 py-2 rounded-md hover:bg-blue-700"
              @click="updateStatus"
            >
              Simpan Status
            </button>
          </div>

          <div class="text-sm border-t pt-3">
            <div class="text-gray-500">Customer</div>
            <div class="font-semibold">{{ order.customer_name }}</div>
            <div class="text-xs text-gray-700 break-all">{{ order.customer_phone }}</div>
            <div class="text-xs text-blue-600">
              <a
                class="underline"
                :href="`https://wa.me/${order.customer_phone?.replace(/[^0-9]/g,'')}`"
                target="_blank"
              >
                Chat via WhatsApp
              </a>
            </div>
          </div>

          <div class="text-sm border-t pt-3">
            <div class="text-gray-500">Alamat Kirim</div>
            <div class="text-xs whitespace-pre-wrap break-words">
              {{ order.customer_address }}
            </div>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div class="border rounded-xl bg-white">
        <div class="p-4 flex items-center justify-between">
          <div class="font-semibold">Item Pesanan</div>
          <div class="text-xs text-gray-500">
            Total Item: {{ items.length }}
          </div>
        </div>

        <div class="divide-y">
          <div
            v-for="it in items"
            :key="it.id"
            class="p-4 grid md:grid-cols-3 gap-4 text-sm"
          >
            <!-- Kiri: nama + note -->
            <div class="md:col-span-1 min-w-0">
              <div class="font-semibold text-gray-800 break-words">
                {{ it.product_name }}
              </div>
              <div class="text-xs text-gray-500 uppercase mt-0.5">
                {{ it.product_type }}
              </div>

              <div v-if="it.note" class="text-xs text-gray-700 bg-yellow-50 border border-yellow-200 rounded mt-2 p-2 whitespace-pre-wrap break-words">
                <div class="text-[10px] text-yellow-700 font-semibold uppercase mb-1">Catatan Customer</div>
                <div class="text-yellow-800">{{ it.note }}</div>
              </div>
            </div>

            <!-- Tengah: spesifikasi -->
            <div class="md:col-span-1 text-xs text-gray-700 leading-relaxed space-y-1 break-words">
              <div class="text-[11px] text-gray-500 font-semibold uppercase">Spesifikasi</div>

              <!-- kita render key-value dari spec_snapshot -->
              <div v-for="(val, key) in it.spec_snapshot" :key="key" class="flex">
                <div class="text-gray-500 w-28 shrink-0 capitalize">
                  {{ key.replace(/_/g,' ') }}
                </div>
                <div class="text-gray-800 break-words">
                  <template v-if="Array.isArray(val)">
                    {{ val.join(', ') }}
                  </template>
                  <template v-else-if="typeof val === 'object' && val !== null">
                    {{ JSON.stringify(val) }}
                  </template>
                  <template v-else>
                    {{ val }}
                  </template>
                </div>
              </div>

              <!-- Breakdown harga khusus item ini -->
              <div class="mt-3">
                <div class="text-[11px] text-gray-500 font-semibold uppercase">Breakdown</div>
                <div v-for="(v,k) in it.pricing_breakdown" :key="k" class="flex">
                  <div class="text-gray-500 w-28 shrink-0 capitalize">{{ k.replace(/_/g,' ') }}</div>
                  <div class="text-gray-800">Rp {{ fmtMoney(v) }}</div>
                </div>
              </div>
            </div>

            <!-- Kanan: qty & harga -->
            <div class="md:col-span-1 text-right flex flex-col justify-between">
              <div>
                <div class="text-gray-500 text-xs">Qty</div>
                <div class="font-semibold text-base">{{ it.qty }}</div>
              </div>

              <div class="mt-3">
                <div class="text-gray-500 text-xs">Harga Satuan</div>
                <div class="font-semibold">Rp {{ fmtMoney(it.unit_price) }}</div>
              </div>

              <div class="mt-3">
                <div class="text-gray-500 text-xs">Subtotal Item</div>
                <div class="font-semibold text-blue-600 text-lg">
                  Rp {{ fmtMoney(it.total_price) }}
                </div>
              </div>
            </div>
          </div>

          <div v-if="!items.length" class="p-4 text-center text-sm text-gray-500">
            Tidak ada item.
          </div>
        </div>
      </div>

      <!-- Footer nav -->
      <div class="flex items-center justify-between text-sm">
        <Link
          :href="route('admin.orders.index')"
          class="text-blue-600 hover:underline"
        >
          ← Kembali ke daftar pesanan
        </Link>

        <!-- (Opsional ke depan: tombol cetak SPK) -->
        <!-- <button class="text-gray-600 border rounded-md px-3 py-1.5 hover:bg-gray-50">
          Cetak SPK
        </button> -->
      </div>
    </div>

</template>
