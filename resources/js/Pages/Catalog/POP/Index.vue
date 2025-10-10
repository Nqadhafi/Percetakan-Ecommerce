<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  products: { type: Array, default: () => [] },
})
</script>

<template>
  <div>
    <h1 class="text-xl font-semibold mb-4">Print On Paper</h1>

    <div v-if="!products.length" class="border rounded-xl bg-white p-6 text-gray-600">
      Belum ada produk.
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <Link
        v-for="p in products"
        :key="p.id"
        :href="route('pop.show', p.slug)"
        class="border rounded-xl bg-white p-4 hover:shadow-sm transition"
      >
        <div class="h-28 bg-gray-100 rounded-md mb-3"></div>
        <div class="font-medium truncate">{{ p.name }}</div>
        <div class="text-xs text-gray-500 mt-1">Klik untuk pilih spesifikasi</div>
      </Link>
    </div>
  </div>
</template>
