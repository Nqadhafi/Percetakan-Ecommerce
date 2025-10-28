<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  filters: { type: Object, required: true },
  counts:  { type: Object, required: true },
  orders:  { type: Object, required: true }, // laravel paginator with data[]
})

const fmtMoney = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
const dateID = s => (s ? new Date(s).toLocaleString('id-ID') : '-')

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

const tabs = computed(() => ([
  { key: null,              label: 'Semua' },
  { key: 'pending',         label: `Menunggu (${props.counts.pending})` },
  { key: 'paid',            label: `Sudah Bayar (${props.counts.paid})` },
  { key: 'in_production',   label: `Produksi (${props.counts.in_production})` },
  { key: 'completed',       label: `Selesai (${props.counts.completed})` },
  { key: 'cancelled',       label: `Batal (${props.counts.cancelled})` },
]));
</script>
<script>
// ⬇⬇⬇ penting: ini override layout bawaan app.js
export default {
  layout: AdminLayout,
}
</script>
<template #header>


      Pesanan


    <div class="space-y-6">
      <!-- Tabs status -->
      <div class="flex flex-wrap gap-2 text-sm">
        <Link
          v-for="t in tabs"
          :key="t.key ?? 'all'"
          :href="route('admin.orders.index', t.key ? { status: t.key } : {})"
          class="px-3 py-1.5 rounded-md border"
          :class="[
            (props.filters.status ?? null) === (t.key ?? null)
              ? 'bg-blue-600 border-blue-600 text-white'
              : 'bg-white hover:bg-gray-50 text-gray-700 border-gray-300'
          ]"
        >
          {{ t.label }}
        </Link>
      </div>

      <!-- Table Orders -->
      <div class="border rounded-xl bg-white overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600 border-b">
            <tr>
              <th class="text-left px-4 py-2 whitespace-nowrap">Kode</th>
              <th class="text-left px-4 py-2 whitespace-nowrap">Tanggal</th>
              <th class="text-left px-4 py-2 whitespace-nowrap">Customer</th>
              <th class="text-left px-4 py-2 whitespace-nowrap">WA</th>
              <th class="text-left px-4 py-2 whitespace-nowrap">Metode</th>
              <th class="text-left px-4 py-2 whitespace-nowrap">Status</th>
              <th class="text-right px-4 py-2 whitespace-nowrap">Total</th>
              <th class="px-4 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="o in orders.data"
              :key="o.id"
              class="border-b last:border-b-0"
            >
              <td class="px-4 py-2 font-semibold text-gray-800">
                {{ o.code }}
              </td>

              <td class="px-4 py-2 text-gray-700">
                {{ dateID(o.created_at) }}
              </td>

              <td class="px-4 py-2 text-gray-700">
                {{ o.customer_name }}
              </td>

              <td class="px-4 py-2 text-gray-700 text-xs">
                <a
                  class="text-blue-600 hover:underline"
                  :href="`https://wa.me/${o.customer_phone?.replace(/[^0-9]/g,'')}`"
                  target="_blank"
                >
                  {{ o.customer_phone }}
                </a>
              </td>

              <td class="px-4 py-2 text-gray-700 uppercase text-xs">
                {{ o.payment_method }}
              </td>

              <td class="px-4 py-2">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border font-medium"
                  :class="badgeClass(o.status)"
                >
                  {{ o.status }}
                </span>
              </td>

              <td class="px-4 py-2 text-right font-semibold">
                Rp {{ fmtMoney(o.total_amount) }}
              </td>

              <td class="px-4 py-2 text-right">
                <Link
                  :href="route('admin.orders.show', o.code)"
                  class="text-blue-600 hover:underline"
                >
                  Detail →
                </Link>
              </td>
            </tr>

            <tr v-if="!orders.data.length">
              <td colspan="8" class="px-4 py-6 text-center text-gray-500 text-sm">
                Tidak ada pesanan untuk filter ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- pagination basic (opsional) -->
      <div class="text-xs text-gray-500" v-if="orders.links">
        <div class="flex flex-wrap gap-2">
          <template v-for="l in orders.links" :key="l.url || l.label">
            <div
              v-if="!l.url"
              class="px-2 py-1 text-gray-400"
              v-html="l.label"
            />
            <Link
              v-else
              class="px-2 py-1 rounded border text-gray-700 hover:bg-gray-50"
              :class="l.active ? 'bg-blue-600 border-blue-600 text-white hover:bg-blue-600' : 'bg-white border-gray-300'"
              :href="l.url"
              v-html="l.label"
            />
          </template>
        </div>
      </div>
    </div>

</template>
