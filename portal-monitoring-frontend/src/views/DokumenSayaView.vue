<template>
  <div class="p-6">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Dokumen Saya</h2>
      <p class="text-gray-500 text-sm mt-1">Daftar dokumen yang ditugaskan kepada Anda</p>
    </div>

    <div class="space-y-3">
      <div v-if="docStore.loading" class="text-center py-12 text-gray-400">Memuat dokumen...</div>

      <div v-else-if="!docStore.documents.length" class="text-center py-12 text-gray-400">
        Tidak ada dokumen yang ditugaskan
      </div>

      <div
        v-for="doc in docStore.documents"
        :key="doc.id"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-start gap-4"
      >
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-1">
            <span class="font-mono text-xs text-gray-400">{{ doc.nomor_dokumen }}</span>
            <StatusBadge :status="doc.status" />
          </div>
          <h3 class="font-semibold text-gray-800">{{ doc.judul }}</h3>
          <div class="flex gap-4 mt-2 text-sm text-gray-500">
            <span>📅 Diterima: {{ formatDate(doc.tanggal_diterima) }}</span>
            <span :class="deadlineClass(doc.tanggal_deadline)">
              ⏰ Deadline: {{ formatDate(doc.tanggal_deadline) }}
            </span>
            <span v-if="doc.tanggal_kembali">
              ✅ Dikembalikan: {{ formatDate(doc.tanggal_kembali) }}
            </span>
          </div>
        </div>
        <div class="text-right">
          <p :class="sisaHariClass(doc.tanggal_deadline)" class="text-lg font-bold">
            {{ sisaHari(doc.tanggal_deadline) }}
          </p>
          <p class="text-xs text-gray-400">hari tersisa</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useDocumentStore } from '@/stores/document'
import { useAuthStore } from '@/stores/auth'
import StatusBadge from '@/components/StatusBadge.vue'

const docStore = useDocumentStore()
const auth = useAuthStore()

onMounted(() => {
  docStore.fetchAll({ pic_id: auth.user?.id })
})

function formatDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function sisaHari(dateStr) {
  if (!dateStr) return '-'
  return Math.ceil((new Date(dateStr) - new Date()) / 86400000)
}

function sisaHariClass(dateStr) {
  const d = sisaHari(dateStr)
  if (d < 0) return 'text-red-600'
  if (d <= 1) return 'text-orange-500'
  return 'text-green-600'
}

function deadlineClass(dateStr) {
  const d = sisaHari(dateStr)
  if (d < 0) return 'text-red-500 font-semibold'
  if (d <= 1) return 'text-orange-500 font-semibold'
  return 'text-gray-500'
}
</script>
