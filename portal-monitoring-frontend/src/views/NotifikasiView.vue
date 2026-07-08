<template>
  <div class="p-6">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Log Notifikasi</h2>
      <p class="text-gray-500 text-sm mt-1">Riwayat pengiriman notifikasi deadline</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-gray-50 text-gray-500 text-left">
            <th class="px-5 py-3 font-medium">Waktu Kirim</th>
            <th class="px-5 py-3 font-medium">Dokumen</th>
            <th class="px-5 py-3 font-medium">Penerima</th>
            <th class="px-5 py-3 font-medium">Tipe</th>
            <th class="px-5 py-3 font-medium">Channel</th>
            <th class="px-5 py-3 font-medium">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="loading">
            <td colspan="6" class="px-5 py-8 text-center text-gray-400">Memuat data...</td>
          </tr>
          <tr v-else-if="!logs.length">
            <td colspan="6" class="px-5 py-8 text-center text-gray-400">Belum ada log notifikasi</td>
          </tr>
          <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50">
            <!-- SESUDAH -->
<td class="px-5 py-3 text-gray-800">{{ log.document?.nomor_dokumen ?? '-' }}</td>
<td class="px-5 py-3 text-gray-600">{{ log.pic?.name ?? '-' }}</td>
<td class="px-5 py-3">
  <span :class="tipeClass(log.notif_type)" class="px-2 py-0.5 rounded-full text-xs font-medium">
    {{ tipeLabel(log.notif_type) }}
  </span>
</td>
<td class="px-5 py-3 text-gray-600 capitalize">{{ log.channel ?? '-' }}</td>
<td class="px-5 py-3">
  <span :class="log.status === 'sent' ? 'text-green-600' : 'text-red-500'" class="text-xs font-medium">
    {{ log.status === 'sent' ? '✓ Terkirim' : '✗ Gagal' }}
  </span>
</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { notifApi } from '@/services/api'

const logs = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await notifApi.list()
    logs.value = res.data.data ?? res.data
  } catch {}
  finally { loading.value = false }
})

function formatDateTime(d) {
  if (!d) return '-'
  return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

// SESUDAH
function tipeLabel(tipe) {
  if (tipe === 'early_warning') return 'H-1'
  if (tipe === 'alert')         return 'Hari H'
  if (tipe === 'overdue')       return 'H+1'
  return tipe ?? '-'
}

function tipeClass(tipe) {
  if (tipe === 'early_warning') return 'bg-yellow-100 text-yellow-700'
  if (tipe === 'alert')         return 'bg-orange-100 text-orange-700'
  if (tipe === 'overdue')       return 'bg-red-100 text-red-700'
  return 'bg-gray-100 text-gray-600'
}
</script>
