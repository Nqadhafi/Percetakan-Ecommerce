<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
})

const fmt = n => new Intl.NumberFormat('id-ID').format(n ?? 0)

const categoryLabel = (cat) => {
  if (cat === 'flyer') return 'Flyer'
  if (cat === 'brosur') return 'Brosur Lipat'
  if (cat === 'kartu_nama') return 'Kartu Nama'
  return cat
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold">Perlengkapan Bisnis</h1>
        <p class="text-sm text-gray-600">Flyer, brosur lipat, kartu nama. Paket siap cetak.</p>
      </div>

      <Link href="/cart" class="text-sm underline">
        Lihat Keranjang →
      </Link>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <Link
        v-for="p in products"
        :key="p.id"
        :href="route('biz.show', p.slug)"
        class="border rounded-xl bg-white hover:shadow-sm transition overflow-hidden block"
      >
        <div class="aspect-[4/3] bg-gray-50 grid place-content-center">
          <img
            v-if="p.thumbnail_url"
            :src="p.thumbnail_url"
            class="object-cover w-full h-full"
            alt=""
          />
          <div v-else class="text-gray-400 text-sm">No Image</div>
        </div>

        <div class="p-4">
          <div class="text-xs text-gray-500 uppercase tracking-wide">
            {{ categoryLabel(p.category) }}
          </div>

          <div class="font-semibold leading-snug mt-1">
            {{ p.name }}
          </div>

          <div class="text-sm text-gray-600 mt-2">
            {{ p.unit_label }}
          </div>

          <div class="mt-3">
            <div class="text-[13px] text-gray-500">Mulai dari</div>
            <div class="text-blue-600 font-semibold">
              Rp {{ fmt(p.base_price) }}
            </div>
          </div>

          <div class="mt-4 text-blue-600 text-sm">
            Lihat detail →
          </div>
        </div>
      </Link>
    </div>
  </div>
</template>
