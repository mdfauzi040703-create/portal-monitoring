<template>
  <div>
    <div class="text-base font-semibold mb-2">Proyek Masuk dari Manager</div>
    <div class="text-xs text-gray-500 mb-5">Daftar proyek yang diberikan manager kepada Anda</div>

    <!-- Tab -->
    <div class="flex gap-2 mb-4">
      <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value"
        :class="activeTab === tab.value ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-500 hover:bg-gray-50'"
        class="text-xs px-3 py-1.5 rounded-lg transition">
        {{ tab.label }}
        <span class="ml-1 font-semibold">{{ tabCount(tab.value) }}</span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-16">
      <div class="flex items-center gap-3 text-gray-400">
        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
        <span class="text-sm">Memuat proyek...</span>
      </div>
    </div>

    <!-- Kosong -->
    <div v-else-if="filtered.length === 0"
      class="flex flex-col items-center justify-center py-16 bg-white border border-dashed border-gray-200 rounded-2xl text-gray-400">
      <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
      </svg>
      <div class="text-sm font-medium">Tidak ada proyek</div>
      <div class="text-xs mt-1 text-center px-8 text-gray-400">
        Belum ada proyek yang diberikan manager kepada Anda
      </div>
    </div>

    <div v-else class="space-y-3">
      <div v-for="doc in filtered" :key="doc.id"
        :class="doc.submit_status === 'assigned' ? 'border-blue-200 bg-blue-50/20' : 'border-gray-200'"
        class="bg-white border rounded-xl p-4 hover:shadow-sm transition">
        <div class="flex items-start gap-4">
          <div class="flex-1 min-w-0">

            <!-- Header -->
            <div class="flex items-center gap-2 flex-wrap mb-2">
              <span class="text-sm font-semibold text-gray-900">{{ doc.nomor_dokumen }}</span>
              <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                {{ doc.project?.name }}
              </span>
              <span :class="submitStatusClass(doc.submit_status)" class="text-xs px-2 py-0.5 rounded-full border font-medium">
                {{ submitStatusLabel(doc.submit_status) }}
              </span>
            </div>

            <!-- Info -->
            <div class="flex items-center gap-4 flex-wrap text-xs text-gray-500 mb-2">
              <span class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Masuk: {{ formatDate(doc.created_at) }}
              </span>
              <span v-if="doc.review_deadline" class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Deadline: <span class="font-medium text-gray-700 ml-0.5">{{ formatDate(doc.review_deadline) }}</span>
              </span>
            </div>

            <!-- Catatan -->
            <div v-if="doc.catatan"
              class="text-xs text-gray-500 italic bg-gray-50 border border-gray-100 rounded-lg px-3 py-2 mb-2">
              "{{ doc.catatan }}"
            </div>

            <!-- File -->
            <div v-if="doc.file_path" class="mb-2">
              <a :href="'/storage/' + doc.file_path" target="_blank"
                class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 hover:underline bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Unduh / Lihat File Proyek
              </a>
            </div>
            <div v-else class="text-xs text-gray-300 mb-2 italic">Tidak ada file terlampir</div>

            <!-- Info PIC -->
            <div v-if="doc.pic"
              class="flex items-center gap-3 text-xs pt-2 border-t border-gray-100 flex-wrap">
              <span class="flex items-center gap-1 text-gray-500">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                PIC: <span class="font-medium text-blue-600 ml-0.5">{{ doc.pic?.name }}</span>
              </span>
              <StatusBadge :status="doc.status" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../axios'
import StatusBadge from '../../components/StatusBadge.vue'

const docs      = ref([])
const loading   = ref(false)
const activeTab = ref('assigned')

const tabs = [
  { label: 'Sedang Dikerjakan', value: 'assigned' },
  { label: 'Selesai',           value: 'selesai' },
  { label: 'Semua',             value: 'semua' },
]

const filtered = computed(() => {
  if (activeTab.value === 'semua') return docs.value
  return docs.value.filter(d => d.submit_status === activeTab.value)
})

function tabCount(val) {
  if (val === 'semua') return docs.value.length
  return docs.value.filter(d => d.submit_status === val).length
}

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function submitStatusLabel(s) {
  return {
    draft:     'Draft',
    submitted: 'Menunggu Assign',
    assigned:  'Sedang Dikerjakan',
    selesai:   'Selesai',
  }[s] || s
}

function submitStatusClass(s) {
  return {
    draft:     'bg-gray-50 text-gray-500 border-gray-200',
    submitted: 'bg-amber-50 text-amber-700 border-amber-200',
    assigned:  'bg-blue-50 text-blue-700 border-blue-200',
    selesai:   'bg-green-50 text-green-700 border-green-200',
  }[s] || 'bg-gray-50 text-gray-500 border-gray-200'
}

onMounted(async () => {
  loading.value = true
  try {
    const res = await api.get('/documents')
    // Ambil dokumen yang sudah diassign ke PIC (berarti sudah diproses manager)
    docs.value = res.data.filter(d =>
      ['assigned', 'selesai'].includes(d.submit_status)
    )
  } finally {
    loading.value = false
  }
})
</script>