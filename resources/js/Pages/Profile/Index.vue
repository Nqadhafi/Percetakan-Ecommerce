<script setup>
import { reactive } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  profile: { type: Object, required: true },
  user: { type: Object, required: true },
  flash: { type: Object, default: () => ({}) },
})

const form = useForm({
  full_name: props.profile.full_name ?? props.user.name ?? '',
  phone: props.profile.phone ?? '',
  company: props.profile.company ?? '',
  tax_id: props.profile.tax_id ?? '',
  address: {
    street: props.profile.address?.street ?? '',
    district: props.profile.address?.district ?? '',
    city: props.profile.address?.city ?? '',
    province: props.profile.address?.province ?? '',
    postal_code: props.profile.address?.postal_code ?? '',
    notes: props.profile.address?.notes ?? '',
  }
})

function submit() {
  form.post(route('profile.update'))
}
</script>

<template>
  <div class="max-w-3xl mx-auto space-y-6">
    <h1 class="text-xl font-semibold">Profil Saya</h1>

    <div v-if="$page.props.flash?.success" class="p-3 rounded-md bg-green-50 border border-green-200 text-green-700">
      {{ $page.props.flash.success }}
    </div>

    <div class="border rounded-xl bg-white p-4 space-y-4">
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Nama Lengkap</label>
          <input v-model="form.full_name" type="text" class="w-full border rounded-md px-3 py-2" />
          <p v-if="form.errors.full_name" class="text-sm text-red-600 mt-1">{{ form.errors.full_name }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Nomor HP</label>
          <input v-model="form.phone" type="text" class="w-full border rounded-md px-3 py-2" />
          <p v-if="form.errors.phone" class="text-sm text-red-600 mt-1">{{ form.errors.phone }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Perusahaan (opsional)</label>
          <input v-model="form.company" type="text" class="w-full border rounded-md px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">NPWP (opsional)</label>
          <input v-model="form.tax_id" type="text" class="w-full border rounded-md px-3 py-2" />
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm text-gray-600 mb-1">Alamat</label>
          <input v-model="form.address.street" placeholder="Jalan / Gang / No." class="w-full border rounded-md px-3 py-2 mb-2" />
          <div class="grid grid-cols-2 gap-3 mb-2">
            <input v-model="form.address.district" placeholder="Kecamatan" class="border rounded-md px-3 py-2" />
            <input v-model="form.address.city" placeholder="Kota/Kabupaten" class="border rounded-md px-3 py-2" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <input v-model="form.address.province" placeholder="Provinsi" class="border rounded-md px-3 py-2" />
            <input v-model="form.address.postal_code" placeholder="Kode Pos" class="border rounded-md px-3 py-2" />
          </div>
          <input v-model="form.address.notes" placeholder="Catatan pengiriman (opsional)" class="w-full border rounded-md px-3 py-2 mt-2" />
        </div>
      </div>

      <div class="flex justify-end">
        <button @click="submit" :disabled="form.processing"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-60">
          Simpan
        </button>
      </div>
    </div>

    <p class="text-xs text-gray-500">Terakhir diperbarui: {{ profile.updated_at ?? '-' }}</p>
  </div>
</template>
