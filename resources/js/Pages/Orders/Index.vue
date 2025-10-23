<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  orders: { type: Object, required: true },
  filters: { type: Object, required: true }, // { status, q }
})

// Mapping tab → status di DB
const tabs = [
  { key: 'pending', label: 'Menunggu Verifikasi', color: 'amber' },
  { key: 'process', label: 'Dalam Proses', color: 'blue' },
  { key: 'completed', label: 'Selesai', color: 'emerald' },
  { key: 'cancelled', label: 'Dibatalkan', color: 'rose' },
]

// Tab aktif berdasar filter.status
const activeTab = ref(props.filters.status || 'pending')

function switchTab(tabKey) {
  activeTab.value = tabKey
  let status = null
  if (tabKey === 'pending') status = 'pending'
  else if (tabKey === 'process') status = ['paid','in_production']
  else if (tabKey === 'completed') status = 'completed'
  else if (tabKey === 'cancelled') status = 'cancelled'

  router.get(route('orders.index'), {
    status: status,
  }, { preserveState: true, replace: true })
}

function fmt(n){ return new Intl.NumberFormat('id-ID').format(n ?? 0) }

const badgeClass = (s) => ({
  pending: 'bg-amber-100 text-amber-800',
  paid: 'bg-blue-100 text-blue-800',
  in_production: 'bg-blue-100 text-blue-800',
  completed: 'bg-emerald-100 text-emerald-800',
  cancelled: 'bg-rose-100 text-rose-800',
}[s] || 'bg-gray-100 text-gray-700')
</script>

<template>
  <div class="max-w-6xl mx-auto py-8 px-4">
    <h1 class="text-xl font-semibold mb-4">Pesanan Saya</h1>

    <!-- Tabs -->
    <div class="flex overflow-x-auto border-b border-gray-200 mb-6">
      <button
        v-for="t in tabs"
        :key="t.key"
        @click="switchTab(t.key)"
        :class="[
          'px-4 py-2 text-sm font-medium border-b-2 -mb-px whitespace-nowrap',
          activeTab === t.key
            ? `border-${t.color}-600 text-${t.color}-600`
            : 'border-transparent text-gray-500 hover:text-gray-700'
        ]"
      >
        {{ t.label }}
      </button>
    </div>

    <!-- Orders Table -->
    <div class="bg-white border rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="text-left font-medium py-3 px-4">Kode</th>
              <th class="text-left font-medium py-3 px-4">Status</th>
              <th class="text-right font-medium py-3 px-4">Total</th>
              <th class="text-left font-medium py-3 px-4">Dibuat</th>
              <th class="text-right font-medium py-3 px-4">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="o in orders.data" :key="o.code" class="border-t">
              <td class="py-3 px-4 font-medium">{{ o.code }}</td>
              <td class="py-3 px-4">
                <span class="px-2 py-1 rounded-md" :class="badgeClass(o.status)">
                  {{ o.status }}
                </span>
              </td>
              <td class="py-3 px-4 text-right">Rp {{ fmt(o.total) }}</td>
              <td class="py-3 px-4">{{ o.created_at }}</td>
              <td class="py-3 px-4 text-right">
                <a :href="o.show_url" class="underline">Detail</a>
              </td>
            </tr>
            <tr v-if="!orders.data.length">
              <td colspan="5" class="py-6 px-4 text-center text-gray-500">
                Tidak ada pesanan pada tab ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between p-3 border-t">
        <div class="text-xs text-gray-500">
          Menampilkan {{ orders.from ?? 0 }}–{{ orders.to ?? 0 }} dari {{ orders.total ?? 0 }}
        </div>
        <div class="flex gap-2">
          <a v-for="link in orders.links" :key="link.url || link.label"
             :href="link.url || '#'"
             :class="[
               'px-3 py-1 rounded-md border text-sm',
               link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50'
             ]"
             v-html="link.label"
             @click.prevent="link.url && router.get(link.url, {}, { preserveState: true })" />
        </div>
      </div>
    </div>
  </div>
</template>
