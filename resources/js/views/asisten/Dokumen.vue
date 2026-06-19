<template>
  <div>
    <div class="text-base font-semibold mb-2">Semua Dokumen</div>
    <div class="text-xs text-gray-500 mb-5">Seluruh proyek yang Anda input, baik diterima maupun dikirim ke Manager</div>

    <!-- Filter -->
    <div class="flex gap-2 mb-4 flex-wrap">
      <select v-model="filter.submit_status" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
        <option value="">Semua status kirim</option>
        <option value="draft">Draft</option>
        <option value="submitted">Menunggu Manager</option>
        <option value="assigned">Sedang Dikerjakan PIC</option>
        <option value="selesai">Selesai</option>
      </select>
      <select v-model="filter.project_id" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
        <option value="">Semua project</option>
        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
      </select>
      <input v-model="filter.search" type="text" placeholder="Cari nomor dokumen..."
        class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs flex-1 min-w-40 focus:outline-none focus:ring-2 focus:ring-blue-400"/>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-4 gap-3 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
        <div class="text-2xl font-semibold text-gray-900">{{ stats.total }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Total</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
        <div class="text-2xl font-semibold text-amber-600">{{ stats.waiting }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Menunggu Manager</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
        <div class="text-2xl font-semibold text-blue-600">{{ stats.running }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Sedang Dikerjakan</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
        <div class="text-2xl font-semibold text-green-600">{{ stats.done }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Selesai</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-16">
      <div class="flex items-center gap-3 text-gray-400">
        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
        <span class="text-sm">Memuat data...</span>
      </div>
    </div>

    <div v-else class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50">
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">#</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nomor Dokumen</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Project</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">PIC</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Deadline</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Tgl Diterima</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">File</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status Kirim</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(doc, i) in filtered" :key="doc.id"
            class="border-b border-gray-50 hover:bg-gray-50 transition">
            <td class="px-4 py-3 text-gray-400 text-xs">{{ String(i+1).padStart(3,'0') }}</td>
            <td class="px-4 py-3 font-medium text-xs">{{ doc.nomor_dokumen }}</td>
            <td class="px-4 py-3">
              <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                {{ doc.project?.name }}
              </span>
            </td>
            <td class="px-4 py-3 text-xs">
              <span v-if="doc.pic" class="text-blue-600 font-medium">{{ doc.pic?.name }}</span>
              <span v-else class="text-gray-300">Belum diassign</span>
            </td>
            <td class="px-4 py-3 text-xs text-gray-500">
              {{ doc.review_deadline ? formatDate(doc.review_deadline) : '—' }}
            </td>
            <td class="px-4 py-3 text-xs" :class="doc.return_actual_date ? 'text-green-600 font-medium' : 'text-gray-300'">
              {{ doc.return_actual_date ? formatDate(doc.return_actual_date) : '—' }}
            </td>
            <td class="px-4 py-3">
              <a v-if="doc.file_path" :href="'/storage/' + doc.file_path" target="_blank"
                class="text-xs text-blue-600 hover:underline">Lihat</a>
              <span v-else class="text-xs text-gray-300">—</span>
            </td>
            <td class="px-4 py-3">
              <span :class="submitStatusClass(doc.submit_status)" class="text-xs px-2 py-0.5 rounded-full border font-medium">
                {{ submitStatusLabel(doc.submit_status) }}
              </span>
            </td>
          </tr>
          <tr v-if="filtered.length === 0">
            <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">Tidak ada dokumen</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../axios'
import { useAuthStore } from '../../stores/auth'

const auth     = useAuthStore()
const myDocs   = ref([])
const projects = ref([])
const loading  = ref(false)
const filter   = ref({ submit_status: '', project_id: '', search: '' })

const filtered = computed(() => {
  return myDocs.value.filter(d => {
    if (filter.value.submit_status && d.submit_status !== filter.value.submit_status) return false
    if (filter.value.project_id && d.project_id !== filter.value.project_id) return false
    if (filter.value.search && !d.nomor_dokumen.toLowerCase().includes(filter.value.search.toLowerCase())) return false
    return true
  })
})

const stats = computed(() => ({
  total:   myDocs.value.length,
  waiting: myDocs.value.filter(d => d.submit_status === 'submitted' || d.submit_status === 'draft').length,
  running: myDocs.value.filter(d => d.submit_status === 'assigned').length,
  done:    myDocs.value.filter(d => d.submit_status === 'selesai').length,
}))

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function submitStatusLabel(s) {
  return {
    draft:    'Draft',
    submitted:'Menunggu Manager',
    assigned: 'Sedang Dikerjakan PIC',
    selesai:  'Selesai'
  }[s] || s
}

function submitStatusClass(s) {
  return {
    draft:    'bg-gray-50 text-gray-500 border-gray-200',
    submitted:'bg-amber-50 text-amber-700 border-amber-200',
    assigned: 'bg-blue-50 text-blue-700 border-blue-200',
    selesai:  'bg-green-50 text-green-700 border-green-200',
  }[s] || ''
}

onMounted(async () => {
  loading.value = true
  try {
    const [dRes, pRes] = await Promise.all([api.get('/documents'), api.get('/projects')])
    myDocs.value   = dRes.data.filter(d => d.asisten_id === auth.user?.id)
    projects.value = pRes.data
  } finally {
    loading.value = false
  }
})
</script>