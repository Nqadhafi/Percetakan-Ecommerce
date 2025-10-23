<!-- resources/js/Pages/Orders/Index.vue -->
<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
const props = defineProps({ filters:Object, orders:Object, statusOptions:Array })
const f = reactive({ status: props.filters.status ?? null, q: props.filters.q ?? '' })
const fmt = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
const badgeClass = s => ({
  pending:'bg-amber-100 text-amber-800',
  paid:'bg-blue-100 text-blue-800',
  in_production:'bg-indigo-100 text-indigo-800',
  completed:'bg-emerald-100 text-emerald-800',
  cancelled:'bg-rose-100 text-rose-800',
}[s] || 'bg-gray-100 text-gray-700')
function applyFilters(){
  router.get(route('orders.index'), { status: f.status || undefined, q: f.q || undefined }, { preserveState:true, replace:true })
}
</script>

<template>
  <div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-semibold">Pesanan Saya</h1>
      <a href="/checkout" class="text-sm underline">Buat pesanan baru</a>
    </div>

    <div class="bg-white border rounded-xl p-4 mb-4">
      <div class="grid sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Status</label>
          <select v-model="f.status" class="w-full border rounded-md px-3 py-2">
            <option v-for="opt in statusOptions" :key="String(opt.value)" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm text-gray-600 mb-1">Cari Kode</label>
          <div class="flex gap-2">
            <input v-model.trim="f.q" class="w-full border rounded-md px-3 py-2" placeholder="ORD-20251020-6528" />
            <button class="px-4 py-2 rounded-md bg-blue-600 text-white" @click="applyFilters">Terapkan</button>
          </div>
        </div>
      </div>
    </div>

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
              <td class="py-3 px-4"><span class="px-2 py-1 rounded-md" :class="badgeClass(o.status)">{{ o.status }}</span></td>
              <td class="py-3 px-4 text-right">Rp {{ fmt(o.total) }}</td>
              <td class="py-3 px-4">{{ o.created_at }}</td>
              <td class="py-3 px-4 text-right">
                <a :href="o.show_url" class="underline">Detail</a>
              </td>
            </tr>
            <tr v-if="!orders.data.length">
              <td colspan="5" class="py-6 px-4 text-center text-gray-500">Belum ada pesanan.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between p-3 border-t">
        <div class="text-xs text-gray-500">Menampilkan {{ orders.from ?? 0 }}–{{ orders.to ?? 0 }} dari {{ orders.total ?? 0 }}</div>
        <div class="flex gap-2">
          <a v-for="link in orders.links" :key="link.url || link.label"
             :href="link.url || '#'"
             :class="['px-3 py-1 rounded-md border text-sm', link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50']"
             v-html="link.label"
             @click.prevent="link.url && router.get(link.url, {}, { preserveState: true })" />
        </div>
      </div>
    </div>
  </div>
</template>
