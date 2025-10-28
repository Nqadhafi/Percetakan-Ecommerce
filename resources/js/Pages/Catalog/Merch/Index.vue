<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
})

const fmt = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold">Merchandise Custom</h1>
        <p class="text-sm text-gray-600">
          Tumblr custom UV, pin, mug custom, dan lainnya. Cocok buat branding & souvenir.
        </p>
      </div>

      <Link href="/cart" class="text-sm underline">
        Lihat Keranjang →
      </Link>
    </div>

    <!-- Grid Produk -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <Link
        v-for="p in products"
        :key="p.id"
        :href="route('merch.show', p.slug)"
        class="border rounded-xl bg-white hover:shadow-sm transition overflow-hidden block"
      >
        <!-- Gambar -->
        <div class="aspect-[4/3] bg-gray-50 grid place-content-center">
          <img
            v-if="p.thumbnail_url"
            :src="p.thumbnail_url"
            class="object-cover w-full h-full"
            alt=""
          />
          <div v-else class="text-gray-400 text-sm">No Image</div>
        </div>

        <!-- Body -->
        <div class="p-4">
          <div class="font-semibold leading-snug">
            {{ p.name }}
          </div>

          <div class="text-xs text-gray-500 mt-1">
            Min. Order:
            <span class="font-medium text-gray-700">{{ p.min_order_qty }} pcs</span>
          </div>

          <div v-if="p.variant_label" class="text-xs text-gray-400 mt-1">
            Varian: {{ p.variant_label }}
          </div>

          <div class="mt-4 text-blue-600 text-sm">
            Lihat detail →
          </div>
        </div>
      </Link>
    </div>
  </div>
</template>
