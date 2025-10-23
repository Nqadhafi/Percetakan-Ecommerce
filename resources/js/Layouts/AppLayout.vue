<script setup>
import { computed, ref } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)
const cartCount = computed(() => page.props.cartCount ?? 0)
const categories = computed(() => page.props.categories ?? [])
const appName = computed(() => page.props.appName ?? 'Shabat Printing')

const q = ref('')
const showMenu = ref(false)
function toggleMenu(){ showMenu.value = !showMenu.value }
function closeMenu(){ showMenu.value = false }
const props = defineProps({
  user: { type: Object, required: false },
})

function onSearch() {
  if (!q.value.trim()) return
  // sementara arahkan ke POP Index + query
  router.visit(route('pop.index', { q: q.value }))
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 text-gray-800">
    <!-- TOP BAR -->
    <div class="w-full bg-white text-sm border-b">
      <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-10 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link :href="route('front.home')" class="hover:underline">Home</Link>
          <Link :href="route('pop.index')" class="hover:underline">Print On Paper</Link>
          <Link :href="route('mmt.index')" class="hover:underline">MMT & Banner</Link>
        </div>
        <div class="flex items-center gap-4">
  <template v-if="user">
    <!-- Wrapper -->
    <div class="relative">
      <!-- Trigger -->
      <button
        type="button"
        @click="toggleMenu"
        class="flex items-center gap-2 rounded-md px-3 py-1 hover:bg-gray-100 focus:outline-none"
      >
        <div class="w-8 h-8 rounded-full bg-gray-200 grid place-content-center text-gray-700">
          <i class="bi bi-person"></i>
        </div>
        <span class="hidden sm:inline text-sm font-medium">Hi, {{ user.name }}</span>
        <i class="bi bi-caret-down-fill text-xs"></i>
      </button>

      <!-- Dropdown -->
      <transition name="fade">
        <div
          v-if="showMenu"
          @click.outside="closeMenu"
          class="absolute right-0 mt-2 w-44 bg-white border rounded-xl shadow-lg py-2 z-50"
        >
          <Link href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            <i class="bi bi-person me-2"></i> Profil
          </Link>
          <Link href="/orders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            <i class="bi bi-receipt me-2"></i> Pesanan Saya
          </Link>
          <hr class="my-1 border-gray-100" />
          <Link href="/logout" method="post" as="button"
                class="block w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50">
            <i class="bi bi-box-arrow-right me-2"></i> Keluar
          </Link>
        </div>
      </transition>
    </div>
  </template>
          <template v-else>
            <Link href="/login" class="hover:underline">Masuk</Link>
            <Link href="/register" class="hover:underline">Daftar</Link>
          </template>
        </div>
      </div>
    </div>

    <!-- HEADER (Shopee-like: logo + search + cart) -->
    <header class="w-full bg-white sticky top-0 z-40 shadow-sm">
      <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3">
        <div class="flex items-center gap-3 sm:gap-6">
          <!-- Logo -->
          <Link :href="route('front.home')" class="shrink-0 flex items-center gap-2">
            <!-- Placeholder logo box -->
            <div class="w-9 h-9 bg-blue-600 rounded-md"></div>
            <div class="font-semibold text-lg leading-tight">{{ appName }}</div>
          </Link>

          <!-- Search (center grow) -->
          <div class="flex-1">
            <form @submit.prevent="onSearch" class="flex">
              <input
                v-model="q"
                type="search"
                placeholder="Cari produk cetak…"
                class="w-full border border-gray-300 rounded-l-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700"
                aria-label="Cari"
              >
                🔍
              </button>
            </form>
            <!-- Suggested keywords (opsional) -->
            <div class="hidden sm:flex flex-wrap gap-2 mt-1 text-xs text-gray-500">
              <button class="hover:underline" @click="q='Kartu Nama'; onSearch()">Kartu Nama</button>
              <button class="hover:underline" @click="q='Brosur'; onSearch()">Brosur</button>
              <button class="hover:underline" @click="q='Banner'; onSearch()">Banner</button>
              <button class="hover:underline" @click="q='Stiker'; onSearch()">Stiker</button>
            </div>
          </div>

          <!-- Cart -->
          <Link href="/cart" class="relative inline-flex items-center gap-2 px-3 py-2 rounded-md border hover:bg-gray-50">
            🛒
            <span>Keranjang</span>
            <span
              v-if="cartCount > 0"
              class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-1.5 leading-5"
            >{{ cartCount }}</span>
          </Link>
        </div>
      </div>

      <!-- CATEGORY BAR -->
      <nav class="border-t bg-white">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
          <ul class="flex gap-4 overflow-x-auto py-2">
            <li v-for="(cat, idx) in categories" :key="idx">
              <Link :href="cat.href" class="text-sm text-gray-700 hover:text-blue-700 whitespace-nowrap">
                {{ cat.name }}
              </Link>
            </li>
          </ul>
        </div>
      </nav>
    </header>

    <!-- MAIN -->
    <main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6">
      <slot />
    </main>

    <!-- FOOTER -->
    <footer class="border-t bg-white">
      <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-8 grid sm:grid-cols-3 gap-6 text-sm">
        <div>
          <div class="font-semibold mb-2">Shabat Printing</div>
          <p class="text-gray-600">Cetak cepat & berkualitas untuk kebutuhan bisnis dan personal.</p>
        </div>
        <div>
          <div class="font-semibold mb-2">Bantuan</div>
          <ul class="space-y-1 text-gray-600">
            <li><a href="#" class="hover:underline">FAQ</a></li>
            <li><a href="#" class="hover:underline">Syarat & Ketentuan</a></li>
            <li><a href="#" class="hover:underline">Kebijakan Privasi</a></li>
          </ul>
        </div>
        <div>
          <div class="font-semibold mb-2">Kontak</div>
          <ul class="space-y-1 text-gray-600">
            <li>WhatsApp: 08xx-xxxx-xxxx</li>
            <li>Email: support@shabatprinting.id</li>
            <li>Surakarta, Indonesia</li>
          </ul>
        </div>
      </div>
      <div class="text-center text-xs text-gray-500 pb-6">
        © {{ new Date().getFullYear() }} {{ appName }}. All rights reserved.
      </div>
    </footer>
  </div>
</template>
<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }</style>