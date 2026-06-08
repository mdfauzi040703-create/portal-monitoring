<template>
  <div class="p-6">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
      <p class="text-gray-500 text-sm mt-1">Ringkasan monitoring dokumen</p>
    </div>

    <!-- Stat cards -->
    <div v-if="loading" class="grid grid-cols-4 gap-4 mb-8">
      <div v-for="i in 4" :key="i" class="bg-white rounded-xl p-5 border border-gray-100 animate-pulse h-24"></div>
    </div>

    <div v-else class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
      <StatCard label="Total Dokumen"     :value="stats.total_dokumen   ?? 0" icon="📄" color="blue" />
      <StatCard label="Menunggu Review"   :value="stats.menunggu        ?? 0" icon="🕐" color="yellow" />
      <StatCard label="Terlambat"         :value="stats.terlambat       ?? 0" icon="⚠️" color="red" />
      <StatCard label="Selesai"           :value="stats.selesai         ?? 0" icon="✅" color="green" />
    </div>

    <!-- Tabel dokumen mendekati deadline -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">Dokumen Mendekati Deadline</h3>
        <RouterLink to="/dokumen" class="text-sm text-blue-600 hover:underline">Lihat semua</RouterLink>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-gray-500 text-left">
              <th class="px-6 py-3 font-medium">No. Dokumen</th>
              <th class="px-6 py-3 font-medium">Judul</th>
              <th class="px-6 py-3 font-medium">PIC</th>
              <th class="px-6 py-3 font-medium">Deadline</th>
              <th class="px-6 py-3 font-medium">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="!dokumenDeadline.length">
              <td colspan="5" class="px-6 py-8 text-center text-gray-400">Tidak ada dokumen mendekati deadline</td>
            </tr>
            <tr v-for="doc in dokumenDeadline" :key="doc.id" class="hover:bg-gray-50">
              <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ doc.nomor_dokumen }}</td>
              <td class="px-6 py-3 text-gray-800">{{ doc.judul }}</td>
              <td class="px-6 py-3 text-gray-600">{{ doc.pic?.name ?? '-' }}</td>
              <td class="px-6 py-3">
                <span :class="deadlineClass(doc.tanggal_deadline)">
                  {{ formatDate(doc.tanggal_deadline) }}
                </span>
              </td>
              <td class="px-6 py-3">
                <StatusBadge :status="doc.status" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { dashboardApi } from '@/services/api'
import StatCard from '@/components/StatCard.vue'
import StatusBadge from '@/components/StatusBadge.vue'

const loading = ref(true)
const stats = ref({})
const dokumenDeadline = ref([])

onMounted(async () => {
  try {
    const res = await dashboardApi.index()
    stats.value = res.data.stats ?? {}
    dokumenDeadline.value = res.data.dokumen_deadline ?? []
  } catch {}
  finally { loading.value = false }
})

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function deadlineClass(dateStr) {
  if (!dateStr) return 'text-gray-500'
  const diff = Math.ceil((new Date(dateStr) - new Date()) / (1000 * 60 * 60 * 24))
  if (diff < 0) return 'text-red-600 font-semibold'
  if (diff <= 1) return 'text-orange-500 font-semibold'
  return 'text-gray-700'
}
</script>
