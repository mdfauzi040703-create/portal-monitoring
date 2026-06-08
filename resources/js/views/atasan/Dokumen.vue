<template>
  <div>
    <div class="text-base font-semibold mb-2">Dokumen Dikirim & Diterima</div>
    <div class="text-xs text-gray-500 mb-5">Dokumen yang saya input dan sudah dikembalikan oleh PIC</div>

    <!-- Filter -->
    <div class="flex gap-2 mb-4 flex-wrap">
      <select v-model="filter.status" @change="filterDocs"
        class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
        <option value="">Semua status</option>
        <option value="selesai">Selesai / Diterima</option>
        <option value="early_warning">Early Warning</option>
        <option value="alert">Alert</option>
        <option value="pending">Pending</option>
      </select>
      <select v-model="filter.project_id" @change="filterDocs"
        class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
        <option value="">Semua project</option>
        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
      </select>
      <input v-model="filter.search" @input="filterDocs" type="text" placeholder="Cari nomor dokumen..."
        class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs flex-1 min-w-40 focus:outline-none focus:ring-2 focus:ring-amber-400"/>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-4 gap-3 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
        <div class="text-2xl font-semibold text-gray-900">{{ stats.total }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Total Dokumen</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
        <div class="text-2xl font-semibold text-green-600">{{ stats.selesai }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Diterima / Selesai</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
        <div class="text-2xl font-semibold text-amber-600">{{ stats.warning }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Early Warning</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
        <div class="text-2xl font-semibold text-red-600">{{ stats.alert }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Alert</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loadingTable" class="flex items-center justify-center py-16">
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
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Project</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nomor Dokumen</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">PIC</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Tgl Input</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Deadline</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Tgl Diterima</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">File</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(doc, i) in filtered" :key="doc.id"
            :class="doc.status === 'alert' ? 'bg-red-50/20' : doc.status === 'early_warning' ? 'bg-amber-50/20' : ''"
            class="border-b border-gray-50 hover:bg-gray-50 transition">
            <td class="px-4 py-3 text-gray-400 text-xs">{{ String(i+1).padStart(3,'0') }}</td>
            <td class="px-4 py-3">
              <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                {{ doc.project?.name }}
              </span>
            </td>
            <td class="px-4 py-3 font-medium">{{ doc.nomor_dokumen }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold flex-shrink-0">
                  {{ initials(doc.pic?.name) }}
                </div>
                <span class="text-xs">{{ doc.pic?.name }}</span>
              </div>
            </td>
            <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ formatDateTime(doc.created_at) }}</td>
            <td class="px-4 py-3 text-xs font-medium" :class="deadlineColor(doc)">
              {{ formatDate(doc.review_deadline) }}
            </td>
            <td class="px-4 py-3 text-xs" :class="doc.return_actual_date ? 'text-green-600 font-medium' : 'text-gray-300'">
              {{ doc.return_actual_date ? formatDate(doc.return_actual_date) : '—' }}
            </td>
            <td class="px-4 py-3">
              <a v-if="doc.file_path" :href="'/storage/' + doc.file_path" target="_blank"
                class="flex items-center gap-1 text-xs text-blue-600 hover:underline">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Unduh
              </a>
              <span v-else class="text-xs text-gray-300">—</span>
            </td>
            <td class="px-4 py-3"><StatusBadge :status="doc.status" /></td>
            <td class="px-4 py-3">
  <button v-if="!doc.return_actual_date" @click="deleteDoc(doc.id)"
    class="text-xs text-red-500 hover:underline transition">
    Hapus
  </button>
  <span v-else class="text-xs text-gray-300">—</span>
</td>
          </tr>
          <tr v-if="filtered.length === 0">
            <td colspan="9" class="px-4 py-10 text-center text-gray-400 text-sm">
              Tidak ada dokumen
            </td>
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
import { useAuthStore } from '../../stores/auth'

const auth        = useAuthStore()
const allDocs     = ref([])
const projects    = ref([])
const loadingTable = ref(false)
const filter      = ref({ status: '', project_id: '', search: '' })

// Hanya dokumen milik atasan yang login
const myDocs = computed(() =>
  allDocs.value.filter(d => d.atasan_id === auth.user?.id)
)

const filtered = computed(() => {
  return myDocs.value.filter(d => {
    if (filter.value.status && d.status !== filter.value.status) return false
    if (filter.value.project_id && d.project_id !== filter.value.project_id) return false
    if (filter.value.search && !d.nomor_dokumen.toLowerCase().includes(filter.value.search.toLowerCase())) return false
    return true
  })
})

const stats = computed(() => ({
  total:   myDocs.value.length,
  selesai: myDocs.value.filter(d => d.status === 'selesai').length,
  warning: myDocs.value.filter(d => d.status === 'early_warning').length,
  alert:   myDocs.value.filter(d => d.status === 'alert').length,
}))

function filterDocs() {} // computed auto update

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function formatDateTime(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleString('id-ID', {
    day:'2-digit', month:'short', year:'numeric',
    hour:'2-digit', minute:'2-digit'
  })
}

function deadlineColor(doc) {
  if (doc.status === 'alert')         return 'text-red-600'
  if (doc.status === 'early_warning') return 'text-amber-600'
  return 'text-gray-700'
}

async function deleteDoc(id) {
  const ok = await openConfirm({
    title:       'Hapus Dokumen',
    message:     'Dokumen yang dihapus tidak bisa dikembalikan.',
    confirmText: 'Ya, Hapus',
    type:        'danger'
  })
  if (!ok) return
  try {
    await api.delete(`/documents/${id}`)
    showToast('Dokumen berhasil dihapus!')
    load()
  } catch {
    showToast('Gagal menghapus dokumen', 'error')
  }
}

onMounted(async () => {
  loadingTable.value = true
  try {
    const [dRes, pRes] = await Promise.all([
      api.get('/documents'),
      api.get('/projects')
    ])
    allDocs.value  = dRes.data
    projects.value = pRes.data
  } finally {
    loadingTable.value = false
  }
})
</script>