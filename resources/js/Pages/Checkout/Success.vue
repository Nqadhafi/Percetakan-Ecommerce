<script setup>
const props = defineProps({
  order: { type: Object, required: true }, // { code,total,status,payment_method,payment_proof_url,created_at }
})
function fmt(n){ return new Intl.NumberFormat('id-ID').format(n ?? 0) }
</script>

<template>
  <div class="max-w-3xl mx-auto py-12 px-4" v-if="order">
    <div class="bg-white border rounded-2xl p-8 text-center">
      <div class="text-3xl mb-2">✅ Terima kasih!</div>
      <p class="text-gray-700">
        Pesanan Anda telah dikirim dan sedang menunggu verifikasi pembayaran.
      </p>

      <div class="mt-6 grid sm:grid-cols-2 gap-4 text-left">
        <div class="border rounded-lg p-4">
          <div class="text-sm text-gray-500">Nomor Order</div>
          <div class="font-semibold">{{ order.code }}</div>
        </div>
        <div class="border rounded-lg p-4">
          <div class="text-sm text-gray-500">Total</div>
          <div class="font-semibold">Rp {{ fmt(order.total) }}</div>
        </div>
        <div class="border rounded-lg p-4">
          <div class="text-sm text-gray-500">Metode Pembayaran</div>
          <div class="font-semibold uppercase">{{ order.payment_method }}</div>
        </div>
        <div class="border rounded-lg p-4">
          <div class="text-sm text-gray-500">Waktu</div>
          <div class="font-semibold">{{ order.created_at }}</div>
        </div>
      </div>

      <div v-if="order.payment_proof_url" class="mt-6">
        <div class="text-sm text-gray-500 mb-1">Bukti Pembayaran</div>
        <a :href="order.payment_proof_url" target="_blank" class="text-blue-600 underline">Lihat bukti</a>
      </div>

      <div class="mt-8">
        <a href="/" class="inline-block px-5 py-2 rounded-md border bg-white hover:bg-gray-50">Kembali ke Beranda</a>
      </div>
    </div>
  </div>
    <div v-else class="max-w-3xl mx-auto py-12 px-4 text-center text-gray-500">
   Memuat detail pesanan...
 </div>
</template>
