<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  products: { type: Array, default: () => [] }, 
});
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-semibold">Sticker A3 / A3+</h1>
      <Link href="/catalog/print-on-paper" class="text-sm underline">Lihat Print On Paper</Link>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <Link
        v-for="p in products" :key="p.id"
        :href="`/catalog/sticker/${p.slug}`"
        class="border rounded-xl bg-white hover:shadow-sm transition overflow-hidden block"
      >
        <div class="aspect-[4/3] bg-gray-50 grid place-content-center">
          <img v-if="p.thumbnail_url" :src="p.thumbnail_url" class="object-cover w-full h-full" alt="" />
          <div v-else class="text-gray-400">No Image</div>
        </div>
        <div class="p-4">
          <div class="font-semibold">{{ p.name }}</div>
          <div class="text-sm text-gray-600">Cetak stiker per lembar A3/A3+</div>
          <div class="mt-3 text-blue-600 text-sm">Lihat detail →</div>
        </div>
      </Link>
    </div>
  </div>
</template>
