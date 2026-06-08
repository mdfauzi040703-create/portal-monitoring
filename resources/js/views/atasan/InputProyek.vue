<template>
  <div>
    <div class="text-base font-semibold mb-4">Input Proyek ke PIC</div>

    <div class="grid grid-cols-2 gap-5">
      <!-- Form input -->
      <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="text-sm font-medium mb-4 text-amber-700">Form Input Dokumen Baru</div>

        <div v-if="errorMsg" class="bg-red-50 border border-red-200 text-red-600 text-xs px-3 py-2 rounded-lg mb-4">
          {{ errorMsg }}
        </div>
        <div v-if="successMsg" class="bg-green-50 border border-green-200 text-green-600 text-xs px-3 py-2 rounded-lg mb-4 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          {{ successMsg }}
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Nomor Dokumen <span class="text-red-400">*</span></label>
            <input v-model="form.nomor_dokumen" type="text" placeholder="cth. SPK-2026/001"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
              :class="{'border-red-300': errors.nomor_dokumen}"/>
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
                + Baru
              </button>
            </div>
            <p v-if="errors.project_id" class="text-xs text-red-500 mt-1">{{ errors.project_id }}</p>
          </div>

          <div>
            <label class="block text-xs text-gray-500 mb-1">Pilih PIC <span class="text-red-400">*</span></label>
            <select v-model="form.pic_id"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
              :class="{'border-red-300': errors.pic_id}">
              <option value="">Pilih PIC yang akan mengerjakan</option>
              <option v-for="u in pics" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
            <p v-if="errors.pic_id" class="text-xs text-red-500 mt-1">{{ errors.pic_id }}</p>
          </div>

          <div>
            <label class="block text-xs text-gray-500 mb-1">Tanggal Masuk <span class="text-red-400">*</span></label>
            <input v-model="form.tanggal_masuk" type="date"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"/>
          </div>

          <div>
            <label class="block text-xs text-gray-500 mb-1">Review Deadline <span class="text-red-400">*</span></label>
            <input v-model="form.review_deadline" type="date"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"/>
            <p class="text-xs text-gray-400 mt-1">PIC akan mendapat notifikasi H-1, Hari H, dan H+1 jika belum selesai.</p>
          </div>

          <!-- Upload file -->
          <div>
            <label class="block text-xs text-gray-500 mb-1">Upload File Dokumen</label>
            <div
              @dragover.prevent="isDragging = true"
              @dragleave="isDragging = false"
              @drop.prevent="onDrop"
              :class="isDragging ? 'border-amber-400 bg-amber-50' : 'border-gray-200 bg-gray-50'"
              class="border-2 border-dashed rounded-xl p-4 text-center cursor-pointer transition"
              @click="$refs.fileInput.click()">
              <input ref="fileInput" type="file" class="hidden"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                @change="onFileChange"/>

              <div v-if="!selectedFile">
                <svg class="w-7 h-7 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <div class="text-xs text-gray-500">Klik atau drag file ke sini</div>
                <div class="text-xs text-gray-400 mt-0.5">PDF, Word, Excel, JPG, PNG — maks 10MB</div>
              </div>

              <div v-else class="flex items-center gap-3 justify-center">
                <svg class="w-7 h-7 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div class="text-left">
                  <div class="text-xs font-medium text-gray-700">{{ selectedFile.name }}</div>
                  <div class="text-xs text-gray-400">{{ fileSize }}</div>
                </div>
                <button @click.stop="selectedFile = null"
                  class="ml-auto text-gray-400 hover:text-red-500 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Upload progress -->
        <div v-if="uploadProgress > 0 && uploadProgress < 100" class="mt-3">
          <div class="flex justify-between text-xs text-gray-500 mb-1">
            <span>Mengupload...</span>
            <span>{{ uploadProgress }}%</span>
          </div>
          <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-amber-500 rounded-full transition-all" :style="{width: uploadProgress + '%'}"></div>
          </div>
        </div>

        <button @click="submit" :disabled="loading"
          class="w-full mt-5 bg-amber-500 text-white rounded-lg py-2.5 text-sm font-medium hover:bg-amber-600 transition disabled:opacity-50 flex items-center justify-center gap-2">
          <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
          {{ loading ? 'Menyimpan...' : 'Kirim ke PIC' }}
        </button>
      </div>

      <!-- Riwayat input -->
      <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="text-sm font-medium mb-4">Riwayat Dokumen yang Saya Input</div>
        <div v-if="myDocs.length === 0" class="text-center py-10 text-gray-400 text-sm">
          Belum ada dokumen yang diinput
        </div>
        <div v-else class="space-y-2 max-h-[500px] overflow-y-auto">
          <div v-for="doc in myDocs" :key="doc.id"
            class="flex items-start gap-3 p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition">
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium">{{ doc.nomor_dokumen }}</div>
              <div class="text-xs text-gray-500 mt-0.5">
                {{ doc.project?.name }} → <span class="text-blue-600">{{ doc.pic?.name }}</span>
              </div>
              <div class="text-xs text-gray-400 mt-0.5">Deadline: {{ formatDate(doc.review_deadline) }}</div>
              <!-- File indicator -->
              <div v-if="doc.file_path" class="mt-1">
                <a :href="'/storage/' + doc.file_path" target="_blank"
                  class="flex items-center gap-1 text-xs text-amber-600 hover:underline w-fit">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                  </svg>
                  Lihat file
                </a>
              </div>
            </div>
            <div class="flex flex-col items-end gap-2">
              <StatusBadge :status="doc.status" />
              <button v-if="!doc.return_actual_date" @click="deleteDoc(doc.id)"
                class="text-xs text-red-400 hover:text-red-600 hover:underline transition">
                Hapus
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal tambah project -->
    <div v-if="showAddProject" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[999]">
      <div class="bg-white rounded-2xl p-6 w-80 border border-gray-200 shadow-xl">
        <div class="text-base font-semibold mb-4">Tambah Project Baru</div>
        <label class="block text-xs text-gray-500 mb-1">Nama Project</label>
        <input v-model="newProject.name" type="text" placeholder="cth. Proyek H"
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"/>
        <div class="flex gap-2 justify-end mt-4">
          <button @click="showAddProject = false" class="text-sm px-4 py-2 border border-gray-200 rounded-lg">Batal</button>
          <button @click="addProject" class="text-sm px-4 py-2 bg-gray-900 text-white rounded-lg">Tambah</button>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast"
      class="fixed bottom-5 right-5 bg-green-600 text-white text-sm px-4 py-3 rounded-xl shadow-lg z-50 flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>
      {{ toast }}
    </div>

    <!-- Confirm modal -->
    <div v-if="showConfirm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[999]">
      <div class="bg-white rounded-2xl p-6 w-80 border border-gray-200 shadow-xl">
        <div class="flex items-center gap-3 mb-3">
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
        <div class="flex gap-2 justify-end mt-4">
          <button @click="showConfirm = false" class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button @click="confirmDelete" class="text-sm px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Ya, Hapus</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../axios'
import StatusBadge from '../../components/StatusBadge.vue'
import { useAuthStore } from '../../stores/auth'

const auth           = useAuthStore()
const projects       = ref([])
const pics           = ref([])
const myDocs         = ref([])
const showAddProject = ref(false)
const showConfirm    = ref(false)
const deleteId       = ref(null)
const loading        = ref(false)
const errorMsg       = ref('')
const successMsg     = ref('')
const errors         = ref({})
const toast          = ref('')
const newProject     = ref({ name: '' })
const selectedFile   = ref(null)
const isDragging     = ref(false)
const uploadProgress = ref(0)
const fileInput      = ref(null)

const form = ref({
  nomor_dokumen: '', project_id: '', pic_id: '',
  tanggal_masuk: '', review_deadline: ''
})

const fileSize = computed(() => {
  if (!selectedFile.value) return ''
  const bytes = selectedFile.value.size
  if (bytes < 1024)      return bytes + ' B'
  if (bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB'
  return (bytes/(1024*1024)).toFixed(1) + ' MB'
})

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function showToastMsg(msg) {
  toast.value = msg
  setTimeout(() => toast.value = '', 3000)
}

function onFileChange(e) {
  const file = e.target.files[0]
  if (file && file.size <= 10 * 1024 * 1024) {
    selectedFile.value = file
  } else if (file) {
    alert('Ukuran file maksimal 10MB')
  }
}

function onDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  if (file && file.size <= 10 * 1024 * 1024) {
    selectedFile.value = file
  } else if (file) {
    alert('Ukuran file maksimal 10MB')
  }
}

async function submit() {
  errorMsg.value   = ''
  successMsg.value = ''
  errors.value     = {}

  if (!form.value.nomor_dokumen)   { errors.value.nomor_dokumen = 'Wajib diisi'; return }
  if (!form.value.project_id)      { errors.value.project_id   = 'Wajib dipilih'; return }
  if (!form.value.pic_id)          { errors.value.pic_id       = 'Wajib dipilih'; return }
  if (!form.value.tanggal_masuk)   { errors.value.tanggal_masuk = 'Wajib diisi'; return }
  if (!form.value.review_deadline) { errors.value.review_deadline = 'Wajib diisi'; return }

  loading.value        = true
  uploadProgress.value = 0

  try {
    const formData = new FormData()
    Object.keys(form.value).forEach(k => formData.append(k, form.value[k]))
    if (selectedFile.value) formData.append('file', selectedFile.value)

    await api.post('/documents', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (e) => {
        uploadProgress.value = Math.round((e.loaded / e.total) * 100)
      }
    })

    successMsg.value = `Dokumen ${form.value.nomor_dokumen} berhasil dikirim ke PIC!`
    form.value       = { nomor_dokumen: '', project_id: '', pic_id: '', tanggal_masuk: '', review_deadline: '' }
    selectedFile.value  = null
    uploadProgress.value = 0
    if (fileInput.value) fileInput.value.value = ''
    loadMyDocs()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Gagal menyimpan'
  } finally {
    loading.value = false
  }
}

async function addProject() {
  if (!newProject.value.name) return
  await api.post('/projects', { name: newProject.value.name })
  const res      = await api.get('/projects')
  projects.value = res.data
  newProject.value     = { name: '' }
  showAddProject.value = false
  showToastMsg('Project berhasil ditambahkan!')
}

function deleteDoc(id) {
  deleteId.value    = id
  showConfirm.value = true
}

async function confirmDelete() {
  showConfirm.value = false
  try {
    await api.delete(`/documents/${deleteId.value}`)
    showToastMsg('Dokumen berhasil dihapus!')
    loadMyDocs()
  } catch {
    showToastMsg('Gagal menghapus dokumen')
  }
}

async function loadMyDocs() {
  const res  = await api.get('/documents')
  myDocs.value = res.data.filter(d => d.atasan_id === auth.user?.id)
}

onMounted(async () => {
  const [pRes, uRes] = await Promise.all([api.get('/projects'), api.get('/users')])
  projects.value = pRes.data
  pics.value     = uRes.data.filter(u => u.role === 'pic')
  loadMyDocs()
})
</script>