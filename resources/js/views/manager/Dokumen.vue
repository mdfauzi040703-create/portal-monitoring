<template>
  <div>
    <div class="text-base font-semibold mb-2">Semua Dokumen</div>
    <div class="text-xs text-gray-500 mb-5">Seluruh proyek yang Anda assign ke PIC, termasuk yang sedang berjalan dan selesai</div>

    <!-- Filter -->
    <div class="flex gap-2 mb-4 flex-wrap">
      <select v-model="filter.status" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
        <option value="">Semua status</option>
        <option value="pending">Pending</option>
        <option value="early_warning">Early Warning</option>
        <option value="alert">Alert</option>
        <option value="selesai">Selesai</option>
      </select>
      <select v-model="filter.project_id" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
        <option value="">Semua project</option>
        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
      </select>
      <input v-model="filter.search" type="text" placeholder="Cari nomor dokumen..."
        class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs flex-1 min-w-40 focus:outline-none focus:ring-2 focus:ring-amber-400"/>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-4 gap-3 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
        <div class="text-2xl font-semibold text-gray-900">{{ stats.total }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Total Proyek</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
        <div class="text-2xl font-semibold text-green-600">{{ stats.selesai }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Selesai</div>
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
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Dari Manager</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">PIC</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Deadline</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Return Actual</th>
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
            <td class="px-4 py-3 font-medium text-xs">{{ doc.nomor_dokumen }}</td>
            <td class="px-4 py-3">
              <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                {{ doc.project?.name }}
              </span>
            </td>
            <td class="px-4 py-3 text-xs text-gray-500">{{ doc.asisten?.name || '—' }}</td>
            <td class="px-4 py-3">
              <div v-if="doc.pic" class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold">
                  {{ initials(doc.pic?.name) }}
                </div>
                <span class="text-xs">{{ doc.pic?.name }}</span>
              </div>
              <span v-else class="text-xs text-gray-300">Belum assign</span>
            </td>
            <td class="px-4 py-3 text-xs font-medium" :class="deadlineColor(doc)">
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
            <td class="px-4 py-3"><StatusBadge :status="doc.status" /></td>
            <td class="px-4 py-3">
  <button v-if="doc.status !== 'selesai'" @click="deleteDoc(doc.id)"
    class="text-xs text-red-500 hover:text-red-700 hover:underline transition">
    Hapus
  </button>
  <span v-else class="text-xs text-gray-300">—</span>
</td>
          </tr>
          <tr v-if="filtered.length === 0">
            <td colspan="9" class="px-4 py-10 text-center text-gray-400 text-sm">Tidak ada dokumen</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal konfirmasi hapus -->
<div v-if="showConfirm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[999]">
  <div class="bg-white rounded-2xl p-6 w-80 border border-gray-200 shadow-xl">
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
      </div>
      <div>
        <div class="text-sm font-semibold">Hapus Dokumen</div>
        <div class="text-xs text-gray-500 mt-0.5">Dokumen yang dihapus tidak bisa dikembalikan.</div>
      </div>
    </div>
    <div class="flex gap-2 justify-end">
      <button @click="showConfirm = false" class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
      <button @click="confirmDelete" class="text-sm px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Ya, Hapus</button>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../axios'
import StatusBadge from '../../components/StatusBadge.vue'

const docs     = ref([])
const projects = ref([])
const loading  = ref(false)
const filter   = ref({ status: '', project_id: '', search: '' })
const showConfirm = ref(false)
const deleteId     = ref(null)

const filtered = computed(() => {
  return docs.value.filter(d => {
    if (filter.value.status && d.status !== filter.value.status) return false
    if (filter.value.project_id && d.project_id !== filter.value.project_id) return false
    if (filter.value.search && !d.nomor_dokumen.toLowerCase().includes(filter.value.search.toLowerCase())) return false
    return true
  })
})

const stats = computed(() => ({
  total:   docs.value.length,
  selesai: docs.value.filter(d => d.status === 'selesai').length,
  warning: docs.value.filter(d => d.status === 'early_warning').length,
  alert:   docs.value.filter(d => d.status === 'alert').length,
}))

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function deadlineColor(doc) {
  if (doc.status === 'alert')         return 'text-red-600'
  if (doc.status === 'early_warning') return 'text-amber-600'
  return 'text-gray-700'
}

function deleteDoc(id) {
  deleteId.value    = id
  showConfirm.value = true
}

async function confirmDelete() {
  showConfirm.value = false
  try {
    await api.delete(`/documents/${deleteId.value}`)
    const [dRes, pRes] = await Promise.all([api.get('/documents'), api.get('/projects')])
    docs.value     = dRes.data
    projects.value = pRes.data
  } catch {
    alert('Gagal menghapus dokumen')
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const [dRes, pRes] = await Promise.all([api.get('/documents'), api.get('/projects')])
    docs.value     = dRes.data
    projects.value = pRes.data
  } finally {
    loading.value = false
  }
})
</script>