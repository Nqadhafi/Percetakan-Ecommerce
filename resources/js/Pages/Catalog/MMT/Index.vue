<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  products: { type: Array, default: () => [] }, // [{id,name,slug}]
})
</script>

<template>
  <div>
    <h1 class="text-xl font-semibold mb-4">MMT & Banner</h1>

    <div v-if="!products.length" class="border rounded-xl bg-white p-6 text-gray-600">
      Belum ada produk MMT.
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <Link
        v-for="p in products"
        :key="p.id"
        :href="route('mmt.show', p.slug)"
        class="border rounded-xl bg-white p-4 hover:shadow-sm transition"
      >
        <div class="h-28 bg-gray-100 rounded-md mb-3 overflow-hidden">
  <img v-if="p.thumbnail_url" :src="p.thumbnail_url" alt="" class="w-full h-full object-cover">
  <div v-else class="w-full h-full bg-gray-100"></div>
</div>

        <div class="font-medium truncate">{{ p.name }}</div>
        <div class="text-xs text-gray-500 mt-1">Klik untuk pilih dimensi & finishing</div>
      </Link>
    </div>
  </div>
</template>
