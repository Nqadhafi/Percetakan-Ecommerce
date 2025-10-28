<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage();
const user = computed(() => page.props?.auth?.user || null);
const role = computed(() => user.value?.role || 'customer');

const isAdmin = computed(() => role.value === 'admin');
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <aside class="w-60 bg-white border-r flex flex-col">
      <div class="px-4 py-4 border-b">
        <div class="font-semibold text-lg">Admin Panel</div>
        <div class="text-xs text-gray-500">
          {{ user?.name }} <span v-if="role" class="uppercase">({{ role }})</span>
        </div>
      </div>

      <nav class="flex-1 px-3 py-4 space-y-2 text-sm">
        <div>
          <div class="text-[11px] text-gray-400 uppercase font-semibold mb-1">
            Operasional
          </div>
          <Link
            :href="route('admin.orders.index')"
            class="block px-3 py-2 rounded-md hover:bg-gray-100 text-gray-700"
          >
            📦 Pesanan
          </Link>
        </div>

        <template v-if="isAdmin">
          <div class="pt-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold mb-1">
              Produk & Harga
            </div>
            <Link
              :href="route('admin.biz.index', {})"
              class="block px-3 py-2 rounded-md hover:bg-gray-100 text-gray-700"
            >
              📰 Brosur / Flyer / Kartu Nama
            </Link>
            <Link
              :href="route('admin.merch.index', {})"
              class="block px-3 py-2 rounded-md hover:bg-gray-100 text-gray-700"
            >
              🎁 Merchandise
            </Link>
          </div>

          <div class="pt-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold mb-1">
              User
            </div>
            <Link
              :href="route('admin.users.index', {})"
              class="block px-3 py-2 rounded-md hover:bg-gray-100 text-gray-700"
            >
              👥 User & Staff
            </Link>
          </div>
        </template>
      </nav>

      <div class="px-4 py-4 border-t text-xs text-gray-500">
        <div class="mb-2">
          <Link href="/" class="hover:underline text-blue-600 text-[13px]">← Kembali ke Storefront</Link>
        </div>
        <div>
          <Link href="/logout" method="post" as="button" class="hover:underline">
            Keluar
          </Link>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 min-w-0">
      <!-- Header -->
      <header class="bg-white border-b px-4 py-3 flex items-center justify-between">
        <div class="font-semibold text-gray-700 text-sm">
          Panel Admin / <slot name="header"></slot>
        </div>
        <div class="text-xs text-gray-500">
          {{ user?.name }} • {{ role }}
        </div>
      </header>

      <!-- Page body -->
      <section class="p-4">
        <slot />
      </section>
    </main>
  </div>
</template>
