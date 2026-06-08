<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <div class="text-base font-semibold">Semua Dokumen</div>
      <button @click="showModal = true"
        class="text-xs bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
        + Tambah Dokumen
      </button>
    </div>

    <!-- Filter -->
    <div class="flex gap-2 mb-4 flex-wrap">
      <select v-model="filter.status" @change="load"
        class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
        <option value="">Semua status</option>
        <option value="selesai">Selesai</option>
        <option value="early_warning">Early Warning</option>
        <option value="alert">Alert</option>
        <option value="pending">Pending</option>
      </select>
      <select v-model="filter.project_id" @change="load"
        class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
        <option value="">Semua project</option>
        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
      </select>
      <input v-model="filter.search" @input="load" type="text" placeholder="Cari nomor dokumen..."
        class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs flex-1 min-w-40 focus:outline-none focus:ring-2 focus:ring-amber-400" />
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
          <tr class="border-b border-gray-100">
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">#</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Project</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nomor Dokumen</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">PIC</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Tgl & Jam Upload</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Deadline</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Return Actual</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(doc, i) in docs" :key="doc.id"
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
                {{ doc.pic?.name }}
              </div>
            </td>
            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ formatDate(doc.created_at) }}</td>
            <td class="px-4 py-3 font-medium">{{ formatDeadline(doc.review_deadline) }}</td>
            <td class="px-4 py-3" :class="doc.return_actual_date ? 'text-green-600 font-medium' : 'text-gray-300'">
              {{ doc.return_actual_date ? formatDeadline(doc.return_actual_date) : '—' }}
            </td>
            <td class="px-4 py-3"><StatusBadge :status="doc.status" /></td>
         <td class="px-4 py-3">
  <div class="flex items-center gap-3">
    <button @click="openReturnModal(doc)"
      v-if="!doc.return_actual_date"
      class="text-xs text-amber-600 hover:underline transition">
      Input Return
    </button>
    <span v-else class="text-xs text-gray-300">—</span>
    <button @click="deleteDoc(doc.id)"
      class="text-xs text-red-500 hover:underline transition">
      Hapus
    </button>
  </div>
</td>
          </tr>
          <tr v-if="docs.length === 0">
            <td colspan="9" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada dokumen</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal tambah dokumen -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-96 border border-gray-200 shadow-xl">
        <div class="text-base font-semibold mb-4">Tambah Dokumen</div>

        <div v-if="errorMsg" class="bg-red-50 border border-red-200 text-red-600 text-xs px-3 py-2 rounded-lg mb-3">
          {{ errorMsg }}
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Nomor Dokumen <span class="text-red-400">*</span></label>
            <input v-model="form.nomor_dokumen" type="text" placeholder="cth. SPK-2025/001"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
              :class="{'border-red-300': errors.nomor_dokumen}" />
            <p v-if="errors.nomor_dokumen" class="text-xs text-red-500 mt-1">{{ errors.nomor_dokumen }}</p>
          </div>

          <div>
            <label class="block text-xs text-gray-500 mb-1">Project <span class="text-red-400">*</span></label>
            <div class="flex gap-2">
              <select v-model="form.project_id"
                class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                :class="{'border-red-300': errors.project_id}">
                <option value="">Pilih project</option>
                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
              <button @click="showAddProject = true"
                class="px-3 py-2 border border-gray-200 rounded-lg text-xs hover:bg-gray-50 transition whitespace-nowrap">
                + Project
              </button>
            </div>
            <p v-if="errors.project_id" class="text-xs text-red-500 mt-1">{{ errors.project_id }}</p>
          </div>

          <div>
            <label class="block text-xs text-gray-500 mb-1">PIC <span class="text-red-400">*</span></label>
            <select v-model="form.pic_id"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
              :class="{'border-red-300': errors.pic_id}">
              <option value="">Pilih PIC</option>
              <option v-for="u in pics" :key="u.id" :value="u.id">{{ u.name }} ({{ u.role }})</option>
            </select>
            <p v-if="errors.pic_id" class="text-xs text-red-500 mt-1">{{ errors.pic_id }}</p>
          </div>

          <div>
            <label class="block text-xs text-gray-500 mb-1">Tanggal Masuk <span class="text-red-400">*</span></label>
            <input v-model="form.tanggal_masuk" type="date"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
          </div>

          <div>
            <label class="block text-xs text-gray-500 mb-1">Review Deadline <span class="text-red-400">*</span></label>
            <input v-model="form.review_deadline" type="date"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
          </div>
        </div>

        <div class="flex gap-2 justify-end mt-5">
          <button @click="closeModal"
            class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Batal
          </button>
          <button @click="addDoc" :disabled="loading"
            class="text-sm px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-700 transition disabled:opacity-50 flex items-center gap-2">
            <svg v-if="loading" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            {{ loading ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal tambah project baru -->
    <div v-if="showAddProject" class="fixed inset-0 bg-black/60 flex items-center justify-center z-[999]">
      <div class="bg-white rounded-2xl p-6 w-80 border border-gray-200 shadow-xl">
        <div class="text-base font-semibold mb-4">Tambah Project Baru</div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Nama Project <span class="text-red-400">*</span></label>
          <input v-model="newProject.name" type="text" placeholder="cth. Proyek G"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
        </div>
        <div class="mt-3">
          <label class="block text-xs text-gray-500 mb-1">Deskripsi (opsional)</label>
          <input v-model="newProject.description" type="text" placeholder="Deskripsi project"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
        </div>
        <div class="flex gap-2 justify-end mt-5">
          <button @click="showAddProject = false"
            class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button @click="addProject" :disabled="loadingProject"
            class="text-sm px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 flex items-center gap-2">
            <svg v-if="loadingProject" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            {{ loadingProject ? 'Menyimpan...' : 'Tambah' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal input return actual date -->
    <div v-if="showReturnModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-80 border border-gray-200 shadow-xl">
        <div class="text-base font-semibold mb-1">Input Return Actual Date</div>
        <div class="text-xs text-gray-500 mb-4">{{ selectedDoc?.nomor_dokumen }} — {{ selectedDoc?.project?.name }}</div>
        <label class="block text-xs text-gray-500 mb-1">Tanggal Selesai <span class="text-red-400">*</span></label>
        <input v-model="returnDate" type="date"
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
        <div class="flex gap-2 justify-end mt-5">
          <button @click="showReturnModal = false"
            class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button @click="submitReturn" :disabled="loadingReturn"
            class="text-sm px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 disabled:opacity-50 flex items-center gap-2">
            <svg v-if="loadingReturn" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            {{ loadingReturn ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Toast sukses -->
    <div v-if="successMsg"
      class="fixed bottom-5 right-5 bg-green-600 text-white text-sm px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 z-50">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>
      {{ successMsg }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../axios'
import StatusBadge from '../../components/StatusBadge.vue'
import { useConfirm } from '../../composables/useConfirm'
import { useToast }   from '../../composables/useToast'

const docs            = ref([])
const projects        = ref([])
const pics            = ref([])
const showModal       = ref(false)
const showAddProject  = ref(false)
const showReturnModal = ref(false)
const selectedDoc     = ref(null)
const returnDate      = ref('')
const loading         = ref(false)
const loadingTable    = ref(false)
const loadingProject  = ref(false)
const loadingReturn   = ref(false)
const errorMsg        = ref('')
const errors          = ref({})
const filter          = ref({ status: '', project_id: '', search: '' })
const form            = ref({ nomor_dokumen: '', project_id: '', pic_id: '', tanggal_masuk: '', review_deadline: '' })
const newProject      = ref({ name: '', description: '' })
const { openConfirm } = useConfirm()
const { showToast }   = useToast()

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

function formatDate(dt) {
  return new Date(dt).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

function formatDeadline(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric'
  })
}

function closeModal() {
  showModal.value = false
  errorMsg.value  = ''
  errors.value    = {}
  form.value      = { nomor_dokumen: '', project_id: '', pic_id: '', tanggal_masuk: '', review_deadline: '' }
}

function openReturnModal(doc) {
  selectedDoc.value     = doc
  returnDate.value      = ''
  showReturnModal.value = true
}

async function load() {
  loadingTable.value = true
  try {
    const params = {}
    if (filter.value.status)     params.status     = filter.value.status
    if (filter.value.project_id) params.project_id = filter.value.project_id
    if (filter.value.search)     params.search     = filter.value.search
    const res  = await api.get('/documents', { params })
    docs.value = res.data
  } finally {
    loadingTable.value = false
  }
}

async function loadProjects() {
  const res      = await api.get('/projects')
  projects.value = res.data
}

async function addDoc() {
  errorMsg.value = ''
  errors.value   = {}

  if (!form.value.nomor_dokumen) { errors.value.nomor_dokumen = 'Nomor dokumen wajib diisi'; return }
  if (!form.value.project_id)    { errors.value.project_id   = 'Project wajib dipilih'; return }
  if (!form.value.pic_id)        { errors.value.pic_id       = 'PIC wajib dipilih'; return }
  if (!form.value.tanggal_masuk) { errors.value.tanggal_masuk = 'Tanggal masuk wajib diisi'; return }
  if (!form.value.review_deadline) { errors.value.review_deadline = 'Deadline wajib diisi'; return }

  loading.value = true
  try {
    await api.post('/documents', form.value)
    closeModal()
    showToast('Dokumen berhasil ditambahkan!')
    load()
  } catch (e) {
    const data = e.response?.data
    if (data?.errors) {
      errors.value = {}
      Object.keys(data.errors).forEach(k => errors.value[k] = data.errors[k][0])
    } else {
      errorMsg.value = data?.message || 'Gagal menyimpan dokumen'
    }
  } finally {
    loading.value = false
  }
}

async function addProject() {
  if (!newProject.value.name) return
  loadingProject.value = true
  try {
    await api.post('/projects', newProject.value)
    await loadProjects()
    newProject.value     = { name: '', description: '' }
    showAddProject.value = false
    showToast('Project berhasil ditambahkan!')
  } catch {
    alert('Gagal menambah project')
  } finally {
    loadingProject.value = false
  }
}

async function submitReturn() {
  if (!returnDate.value) return
  loadingReturn.value = true
  try {
    await api.put(`/documents/${selectedDoc.value.id}`, {
      return_actual_date: returnDate.value
    })
    showReturnModal.value = false
    showToast('Return actual date berhasil diisi!')
    load()
  } catch {
    alert('Gagal menyimpan')
  } finally {
    loadingReturn.value = false
  }
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
    const [pRes, uRes] = await Promise.all([
      api.get('/projects'),
      api.get('/users')
    ])
    projects.value = pRes.data
    pics.value     = uRes.data
  } finally {
    loadingTable.value = false
  }
  load()
})
</script>
