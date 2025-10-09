<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
</script>

<template>
    <!-- Hero -->
    <section class="bg-gradient-to-r from-blue-50 to-white rounded-xl p-6 border">
      <div class="grid md:grid-cols-2 gap-6 items-center">
        <div>
          <h1 class="text-2xl font-semibold leading-tight">Selamat datang di Shabat Printing</h1>
          <p class="text-gray-600 mt-2">Cetak cepat, harga jelas, hasil mantap. Pilih kategori di bawah untuk mulai.</p>
          <div class="mt-4 flex gap-3">
            <inertia-link href="/catalog/print-on-paper" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
              Print On Paper
            </inertia-link>
            <inertia-link href="/catalog/mmt" class="px-4 py-2 border rounded-md hover:bg-gray-50">
              MMT & Banner
            </inertia-link>
          </div>
        </div>
        <div class="h-40 md:h-48 bg-white border rounded-lg"></div>
      </div>
    </section>

    <!-- Kategori tiles -->
    <section class="mt-6">
      <h2 class="font-semibold mb-3">Kategori Populer</h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <inertia-link href="/catalog/print-on-paper" class="border rounded-xl p-4 bg-white hover:shadow-sm">
          <div class="h-28 bg-gray-100 rounded-md mb-3"></div>
          <div class="font-medium">Print On Paper</div>
          <div class="text-sm text-gray-500">Kartu nama, brosur, flyer, sertifikat…</div>
        </inertia-link>
        <inertia-link href="/catalog/mmt" class="border rounded-xl p-4 bg-white hover:shadow-sm">
          <div class="h-28 bg-gray-100 rounded-md mb-3"></div>
          <div class="font-medium">MMT & Banner</div>
          <div class="text-sm text-gray-500">Banner frontlit, albatros, paket tiang…</div>
        </inertia-link>
        <div class="border rounded-xl p-4 bg-white opacity-60 pointer-events-none">
          <div class="h-28 bg-gray-100 rounded-md mb-3"></div>
          <div class="font-medium">Stiker (coming soon)</div>
          <div class="text-sm text-gray-500">Vinyl, kraft, transparan…</div>
        </div>
        <div class="border rounded-xl p-4 bg-white opacity-60 pointer-events-none">
          <div class="h-28 bg-gray-100 rounded-md mb-3"></div>
          <div class="font-medium">Photobook (coming soon)</div>
          <div class="text-sm text-gray-500">Buku kenangan, album acara…</div>
        </div>
      </div>
    </section>
</template>
