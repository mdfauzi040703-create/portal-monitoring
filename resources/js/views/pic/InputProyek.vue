<template>
  <div>
    <div class="text-base font-semibold mb-2">Input / Kirim Proyek</div>
    <div class="text-xs text-gray-500 mb-5">Dokumen yang sudah diselesaikan dan dikirim ke atasan</div>

    <!-- Proyek belum selesai -->
    <div class="mb-6">
      <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Proyek yang Harus Diselesaikan</div>
      <div v-if="belumSelesai.length === 0"
        class="flex flex-col items-center justify-center py-10 bg-white border border-dashed border-gray-200 rounded-xl text-gray-400">
        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="text-sm font-medium">Anda tidak memiliki proyek hari ini</div>
        <div class="text-xs mt-1">Semua proyek sudah selesai atau belum ada tugas baru</div>
      </div>
      <div v-else class="space-y-2">
        <div v-for="doc in belumSelesai" :key="doc.id"
          :class="doc.status === 'alert' ? 'border-red-200 bg-red-50/40' : doc.status === 'early_warning' ? 'border-amber-200 bg-amber-50/40' : 'border-gray-200 bg-white'"
          class="flex items-center gap-4 p-4 border rounded-xl">
          <div class="flex-1">
            <div class="text-sm font-medium">{{ doc.nomor_dokumen }}</div>
            <div class="text-xs text-gray-500 mt-0.5">{{ doc.project?.name }} — dari {{ doc.atasan?.name }}</div>
            <div class="text-xs mt-1" :class="deadlineColor(doc)">Deadline: {{ formatDate(doc.review_deadline) }}</div>
          </div>
          <StatusBadge :status="doc.status" />
          <button @click="openReturn(doc)"
            class="text-xs bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition whitespace-nowrap">
            Kirim / Selesai
          </button>
        </div>
      </div>
    </div>

    <!-- Dokumen sudah dikirim -->
    <div>
      <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Dokumen yang Sudah Dikirim & Diterima</div>
      <div v-if="sudahSelesai.length === 0"
        class="text-center py-8 bg-white border border-dashed border-gray-200 rounded-xl text-gray-400 text-sm">
        Belum ada dokumen yang dikirim
      </div>
      <div v-else class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
              <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">#</th>
              <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nomor Dokumen</th>
              <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Project</th>
              <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Deadline</th>
              <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Tanggal Kirim</th>
              <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">File</th>
              <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(doc, i) in sudahSelesai" :key="doc.id"
              class="border-b border-gray-50 hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-400 text-xs">{{ String(i+1).padStart(3,'0') }}</td>
              <td class="px-4 py-3 font-medium">{{ doc.nomor_dokumen }}</td>
              <td class="px-4 py-3">
                <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                  {{ doc.project?.name }}
                </span>
              </td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ formatDate(doc.review_deadline) }}</td>
              <td class="px-4 py-3 text-xs text-green-600 font-medium">{{ formatDate(doc.return_actual_date) }}</td>
              <td class="px-4 py-3">
                <a v-if="doc.file_path" :href="'/storage/' + doc.file_path" target="_blank"
                  class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Unduh
                </a>
                <span v-else class="text-xs text-gray-300">—</span>
              </td>
              <td class="px-4 py-3"><StatusBadge :status="doc.status" /></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal kirim/selesai dengan upload file -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-96 border border-gray-200 shadow-xl">
        <div class="text-base font-semibold mb-1">Kirim / Tandai Selesai</div>
        <div class="text-xs text-gray-500 mb-5">
          <span class="font-medium text-gray-700">{{ selectedDoc?.nomor_dokumen }}</span>
          — {{ selectedDoc?.project?.name }}
        </div>

        <div class="space-y-4">
          <!-- Tanggal selesai -->
          <div>
            <label class="block text-xs text-gray-500 mb-1">Tanggal Selesai <span class="text-red-400">*</span></label>
            <input v-model="returnDate" type="date"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400"/>
          </div>

          <!-- Upload file -->
          <div>
            <label class="block text-xs text-gray-500 mb-1">Upload File Dokumen</label>
            <div
              @dragover.prevent="isDragging = true"
              @dragleave="isDragging = false"
              @drop.prevent="onDrop"
              :class="isDragging ? 'border-green-400 bg-green-50' : 'border-gray-200 bg-gray-50'"
              class="border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition"
              @click="$refs.fileInput.click()">
              <input ref="fileInput" type="file" class="hidden"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                @change="onFileChange"/>

              <div v-if="!selectedFile">
                <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <div class="text-xs text-gray-500">Klik atau drag file ke sini</div>
                <div class="text-xs text-gray-400 mt-1">PDF, Word, Excel, JPG, PNG</div>
              </div>

              <div v-else class="flex items-center gap-3 justify-center">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <p class="text-xs text-gray-400 mt-1">Opsional — maksimal 10MB</p>
          </div>

          <!-- Upload progress -->
          <div v-if="uploadProgress > 0 && uploadProgress < 100" class="mt-2">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
              <span>Mengupload...</span>
              <span>{{ uploadProgress }}%</span>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full bg-green-500 rounded-full transition-all" :style="{width: uploadProgress + '%'}"></div>
            </div>
          </div>
        </div>

        <div class="flex gap-2 justify-end mt-5">
          <button @click="closeModal" class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">
            Batal
          </button>
          <button @click="submitReturn" :disabled="loadingReturn || !returnDate"
            class="text-sm px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 flex items-center gap-2">
            <svg v-if="loadingReturn" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            {{ loadingReturn ? 'Mengirim...' : 'Kirim Sekarang' }}
          </button>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../axios'
import StatusBadge from '../../components/StatusBadge.vue'
import { useAuthStore } from '../../stores/auth'

const auth           = useAuthStore()
const myDocs         = ref([])
const showModal      = ref(false)
const selectedDoc    = ref(null)
const returnDate     = ref('')
const selectedFile   = ref(null)
const isDragging     = ref(false)
const loadingReturn  = ref(false)
const uploadProgress = ref(0)
const toast          = ref('')
const fileInput      = ref(null)

const belumSelesai = computed(() => myDocs.value.filter(d => !d.return_actual_date))
const sudahSelesai = computed(() => myDocs.value.filter(d => d.return_actual_date))

const fileSize = computed(() => {
  if (!selectedFile.value) return ''
  const bytes = selectedFile.value.size
  if (bytes < 1024)       return bytes + ' B'
  if (bytes < 1024*1024)  return (bytes/1024).toFixed(1) + ' KB'
  return (bytes/(1024*1024)).toFixed(1) + ' MB'
})

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function deadlineColor(doc) {
  if (doc.status === 'alert')         return 'text-red-600'
  if (doc.status === 'early_warning') return 'text-amber-600'
  return 'text-gray-500'
}

function openReturn(doc) {
  selectedDoc.value   = doc
  returnDate.value    = ''
  selectedFile.value  = null
  uploadProgress.value = 0
  showModal.value     = true
}

function closeModal() {
  showModal.value     = false
  selectedFile.value  = null
  uploadProgress.value = 0
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

function showToast(msg) {
  toast.value = msg
  setTimeout(() => toast.value = '', 3000)
}

async function submitReturn() {
  if (!returnDate.value) return
  loadingReturn.value  = true
  uploadProgress.value = 0

  try {
    const formData = new FormData()
    formData.append('return_actual_date', returnDate.value)
    formData.append('_method', 'PUT')
    if (selectedFile.value) {
      formData.append('file', selectedFile.value)
    }

    await api.post(`/documents/${selectedDoc.value.id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (e) => {
        uploadProgress.value = Math.round((e.loaded / e.total) * 100)
      }
    })

    closeModal()
    showToast('Dokumen berhasil dikirim!')
    const res  = await api.get('/documents')
    myDocs.value = res.data.filter(d => d.pic_id === auth.user?.id)
  } catch {
    alert('Gagal mengirim dokumen')
  } finally {
    loadingReturn.value = false
  }
}

onMounted(async () => {
  const res  = await api.get('/documents')
  myDocs.value = res.data.filter(d => d.pic_id === auth.user?.id)
})
</script>