<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

/**
 * Props yang dikirim dari controller (simple & aman dulu):
 * - stats: { pending:number, in_production:number, completed:number }
 * - recentOrders: [{id, code, created_at, status, total}]
 * - profile: { full_name?, phone?, address? }  // untuk progress
 */
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({ pending: 0, in_production: 0, completed: 0 }),
  },
  recentOrders: { type: Array, default: () => [] },
  profile: { type: Object, default: () => ({}) },
})

const profilePercent = computed(() => {
  let score = 0
  if (props.profile?.full_name) score += 40
  if (props.profile?.phone) score += 30
  if (props.profile?.address?.street) score += 30
  return Math.min(100, score)
})

const fmtMoney = n => new Intl.NumberFormat('id-ID').format(n ?? 0)
const dateID = s => (s ? new Date(s).toLocaleString('id-ID') : '-')
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold">Dashboard Pelanggan</h1>
      <div class="flex items-center gap-2">
        <Link href="/catalog/print-on-paper" class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm">
          Belanja POP
        </Link>
        <Link href="/cart" class="px-3 py-1.5 rounded-md border hover:bg-gray-50 text-sm">
          Lihat Keranjang
        </Link>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid sm:grid-cols-3 gap-4">
      <div class="stat-card">
        <div class="stat-icon">🕒</div>
        <div class="stat-body">
          <div class="label">Menunggu Diproses</div>
          <div class="value">{{ props.stats.pending }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🛠️</div>
        <div class="stat-body">
          <div class="label">Sedang Produksi</div>
          <div class="value">{{ props.stats.in_production }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-body">
          <div class="label">Selesai</div>
          <div class="value">{{ props.stats.completed }}</div>
        </div>
      </div>
    </div>

    <!-- 2-col: profil + quick actions -->
    <div class="grid lg:grid-cols-3 gap-4">
      <!-- Profil -->
      <div class="lg:col-span-2 border rounded-xl bg-white p-4">
        <div class="flex items-center justify-between">
          <div>
            <div class="font-semibold">Status Profil</div>
            <div class="text-sm text-gray-600">Lengkapi profil untuk mempercepat checkout</div>
          </div>
          <Link href="/profile" class="text-blue-600 text-sm hover:underline">Kelola Profil</Link>
        </div>

        <div class="mt-4">
          <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-blue-600" :style="{ width: profilePercent + '%' }"></div>
          </div>
          <div class="text-xs text-gray-500 mt-1">{{ profilePercent }}% lengkap</div>

          <ul class="mt-3 text-sm text-gray-600 space-y-1">
            <li>• Nama lengkap: <b>{{ props.profile?.full_name ?? '—' }}</b></li>
            <li>• Nomor HP: <b>{{ props.profile?.phone ?? '—' }}</b></li>
            <li>• Alamat: <b>{{ props.profile?.address?.street ?? '—' }}</b></li>
          </ul>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="border rounded-xl bg-white p-4 space-y-3">
        <div class="font-semibold">Aksi Cepat</div>
        <Link href="/catalog/print-on-paper" class="qa-item">📄 Cetak Print On Paper</Link>
        <Link href="/catalog/mmt" class="qa-item">🖼️ Cetak MMT & Banner</Link>
        <Link href="/cart" class="qa-item">🛒 Lanjutkan Keranjang</Link>
        <Link href="/profile" class="qa-item">👤 Perbarui Profil</Link>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="border rounded-xl bg-white">
      <div class="flex items-center justify-between p-4">
        <div class="font-semibold">Pesanan Terbaru</div>
        <Link href="#" class="text-sm text-blue-600 hover:underline opacity-60 pointer-events-none">Semua Pesanan (coming soon)</Link>
      </div>

      <div v-if="!recentOrders.length" class="p-4 text-gray-600">
        Belum ada pesanan. <Link href="/catalog/print-on-paper" class="text-blue-600 hover:underline">Mulai belanja sekarang</Link>.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm border-t">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="text-left px-4 py-2">Kode</th>
              <th class="text-left px-4 py-2">Tanggal</th>
              <th class="text-left px-4 py-2">Status</th>
              <th class="text-right px-4 py-2">Total</th>
              <th class="px-4 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="o in recentOrders" :key="o.id" class="border-t">
              <td class="px-4 py-2 font-medium">{{ o.code }}</td>
              <td class="px-4 py-2">{{ dateID(o.created_at) }}</td>
              <td class="px-4 py-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border"
                      :class="{
                        'bg-yellow-50 border-yellow-200 text-yellow-700': o.status === 'pending',
                        'bg-blue-50 border-blue-200 text-blue-700': o.status === 'in_production',
                        'bg-green-50 border-green-200 text-green-700': o.status === 'completed'
                      }">
                  {{ o.status }}
                </span>
              </td>
              <td class="px-4 py-2 text-right">Rp {{ fmtMoney(o.total) }}</td>
              <td class="px-4 py-2 text-right">
                <Link :href="`#`" class="text-blue-600 hover:underline opacity-60 pointer-events-none">Lihat (coming soon)</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<style scoped>
.stat-card{ @apply border rounded-xl bg-white p-4 flex items-center gap-3 }
.stat-icon{ @apply w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-lg }
.stat-body .label{ @apply text-sm text-gray-600 }
.stat-body .value{ @apply text-xl font-semibold }

.qa-item{ @apply block w-full px-3 py-2 rounded-md border hover:bg-gray-50 }
</style>
