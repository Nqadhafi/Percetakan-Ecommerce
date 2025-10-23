<!-- resources/js/Pages/Orders/Show.vue -->
<script setup>
const props = defineProps({ order:Object, items:Array })
const fmt = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
</script>

<template>
  <div class="max-w-5xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-semibold">Detail Pesanan — {{ order.code }}</h1>
      <a href="/orders" class="text-sm underline">Kembali</a>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-4">
        <div class="bg-white border rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm text-gray-500">Status</div>
              <div class="font-semibold">{{ order.status }}</div>
            </div>
            <div class="text-right">
              <div class="text-sm text-gray-500">Total</div>
              <div class="font-semibold text-lg">Rp {{ fmt(order.total) }}</div>
            </div>
          </div>
          <div class="grid sm:grid-cols-3 gap-3 mt-4 text-sm">
            <div>
              <div class="text-gray-500">Metode</div>
              <div class="font-medium uppercase">{{ order.payment_method }}</div>
            </div>
            <div>
              <div class="text-gray-500">Dibuat</div>
              <div class="font-medium">{{ order.created_at }}</div>
            </div>
            <div v-if="order.payment_proof_url">
              <div class="text-gray-500">Bukti</div>
              <a :href="order.payment_proof_url" target="_blank" class="text-blue-600 underline">Lihat</a>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-xl p-4">
          <h2 class="font-semibold mb-3">Item</h2>
          <div class="divide-y">
            <div v-for="(it, idx) in items" :key="idx" class="py-3 flex items-start justify-between gap-4">
              <div>
                <div class="font-medium">{{ it.name }}</div>
                <div class="text-sm text-gray-600">
                  <span v-if="it.spec && it.spec.material">{{ it.spec.material }}</span>
                  <span v-if="it.note"> • catatan: {{ it.note }}</span>
                </div>
                <div class="text-xs text-gray-500 mt-1">Qty: {{ it.qty }}</div>
              </div>
              <div class="text-right">
                <div class="text-sm text-gray-500">Harga</div>
                <div class="font-semibold">Rp {{ fmt(it.unit_price) }}</div>
                <div class="text-sm text-gray-500 mt-1">Subtotal</div>
                <div class="font-semibold">Rp {{ fmt(it.total_price) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <div class="bg-white border rounded-xl p-4">
          <h2 class="font-semibold mb-2">Alamat</h2>
          <div class="text-sm">
            <div class="font-medium">{{ order.customer_name }}</div>
            <div class="text-gray-600">{{ order.customer_phone }}</div>
            <div class="mt-1">{{ order.customer_address }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
