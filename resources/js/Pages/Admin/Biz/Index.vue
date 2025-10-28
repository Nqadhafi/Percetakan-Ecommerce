<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
})

const badgeClass = active =>
  active
    ? 'bg-green-50 text-green-700 border-green-200'
    : 'bg-gray-100 text-gray-600 border-gray-200'

const fmtMoney = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
</script>

<script>
export default {
  layout: AdminLayout,
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <div class="font-semibold text-lg">Produk Cetak Bisnis</div>
        <div class="text-xs text-gray-500">
          Kartu nama, flyer, brosur lipat, dsb.
        </div>
      </div>

      <Link
        :href="route('admin.biz.create')"
        class="inline-block px-3 py-2 rounded-md bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700"
      >
        + Produk Baru
      </Link>
    </div>

    <div class="border rounded-xl bg-white overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600 border-b">
          <tr>
            <th class="text-left px-4 py-2">Nama</th>
            <th class="text-left px-4 py-2">Slug</th>
            <th class="text-left px-4 py-2">Kategori</th>
            <th class="text-left px-4 py-2">Harga Dasar</th>
            <th class="text-left px-4 py-2">Unit</th>
            <th class="text-left px-4 py-2">Status</th>
            <th class="px-4 py-2 text-right"></th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="p in products"
            :key="p.id"
            class="border-b last:border-b-0"
          >
            <td class="px-4 py-2 font-semibold text-gray-800">
              {{ p.name }}
            </td>

            <td class="px-4 py-2 text-xs text-gray-700 break-all">
              {{ p.slug }}
            </td>

            <td class="px-4 py-2 text-gray-700 capitalize text-xs">
              {{ p.category.replace('_',' ') }}
            </td>

            <td class="px-4 py-2 text-gray-700">
              Rp {{ fmtMoney(p.base_price) }}
            </td>

            <td class="px-4 py-2 text-gray-700 text-xs">
              {{ p.unit_label }}
            </td>

            <td class="px-4 py-2">
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border font-medium"
                :class="badgeClass(p.is_active)"
              >
                {{ p.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>

            <td class="px-4 py-2 text-right">
              <Link
                :href="route('admin.biz.edit', p.id)"
                class="text-blue-600 hover:underline text-sm"
              >
                Edit →
              </Link>
            </td>
          </tr>

          <tr v-if="!products.length">
            <td colspan="7" class="px-4 py-6 text-center text-gray-500 text-sm">
              Belum ada produk bizprint.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>
