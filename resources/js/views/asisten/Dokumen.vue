<template>
  <div>
    <div class="text-base font-semibold mb-2">Semua Dokumen</div>
    <div class="text-xs text-gray-500 mb-5">Daftar semua dokumen yang diberikan manager kepada Anda</div>

    <!-- Filter & Search -->
    <div class="flex gap-2 mb-4 flex-wrap">
      <input v-model="search" type="text" placeholder="Cari nomor dokumen..."
        class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400 w-52"/>
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
        <span class="text-sm">Memuat dokumen...</span>
      </div>
    </div>

    <!-- Tabel -->
    <div v-else class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50">
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">#</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nomor Dokumen</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Project</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">PIC</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Deadline</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Return Actual</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status Kirim</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filtered.length === 0">
            <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">
              Tidak ada dokumen ditemukan
            </td>
          </tr>
          <tr v-for="(doc, i) in filtered" :key="doc.id"
            class="border-b border-gray-50 hover:bg-gray-50 transition"
            :class="doc.status === 'alert' ? 'bg-red-50/30' : doc.status === 'early_warning' ? 'bg-amber-50/30' : ''">
            <td class="px-4 py-3 text-gray-400 text-xs">{{ String(i+1).padStart(3,'0') }}</td>
            <td class="px-4 py-3 font-medium text-xs">{{ doc.nomor_dokumen }}</td>
            <td class="px-4 py-3">
              <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                {{ doc.project?.name || '—' }}
              </span>
            </td>
            <td class="px-4 py-3 text-xs">
              <span v-if="doc.pic" class="text-blue-600 font-medium">{{ doc.pic?.name }}</span>
              <span v-else class="text-gray-300">Belum diassign</span>
            </td>
            <td class="px-4 py-3 text-xs font-medium" :class="deadlineColor(doc)">
              {{ doc.review_deadline ? formatDate(doc.review_deadline) : '—' }}
            </td>
            <td class="px-4 py-3 text-xs" :class="doc.return_actual_date ? 'text-green-600 font-medium' : 'text-gray-300'">
              {{ doc.return_actual_date ? formatDate(doc.return_actual_date) : '—' }}
            </td>
            <td class="px-4 py-3">
              <span :class="submitStatusClass(doc.submit_status)" class="text-xs px-2 py-0.5 rounded-full border font-medium">
                {{ submitStatusLabel(doc.submit_status) }}
              </span>
            </td>
            <td class="px-4 py-3"><StatusBadge :status="doc.status" /></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../axios'
import StatusBadge from '../../components/StatusBadge.vue'

const docs      = ref([])
const loading   = ref(false)
const activeTab = ref('semua')
const search    = ref('')

const tabs = [
  { label: 'Semua',             value: 'semua' },
  { label: 'Menunggu Assign',   value: 'submitted' },
  { label: 'Sedang Dikerjakan', value: 'assigned' },
  { label: 'Selesai',           value: 'selesai' },
]

const filtered = computed(() => {
  let result = docs.value
  if (activeTab.value !== 'semua') {
    result = result.filter(d => d.submit_status === activeTab.value)
  }
  if (search.value) {
    result = result.filter(d =>
      d.nomor_dokumen?.toLowerCase().includes(search.value.toLowerCase())
    )
  }
  return result
})

function tabCount(val) {
  if (val === 'semua') return docs.value.length
  return docs.value.filter(d => d.submit_status === val).length
}

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function deadlineColor(doc) {
  if (doc.status === 'alert' || doc.status === 'overdue') return 'text-red-600'
  if (doc.status === 'early_warning') return 'text-amber-600'
  return 'text-gray-700'
}

function submitStatusLabel(s) {
  return {
    draft:     'Draft',
    submitted: 'Menunggu Assign',
    assigned:  'Sedang Dikerjakan PIC',
    selesai:   'Selesai',
  }[s] || s
}

function submitStatusClass(s) {
  return {
    draft:     'bg-gray-50 text-gray-500 border-gray-200',
    submitted: 'bg-amber-50 text-amber-700 border-amber-200',
    assigned:  'bg-blue-50 text-blue-700 border-blue-200',
    selesai:   'bg-green-50 text-green-700 border-green-200',
  }[s] || ''
}

onMounted(async () => {
  loading.value = true
  try {
    const res  = await api.get('/documents')
    docs.value = res.data
  } finally {
    loading.value = false
  }
})
</script>