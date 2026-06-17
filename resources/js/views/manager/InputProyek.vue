<template>
  <div>
    <div class="text-base font-semibold mb-4">Input Dokumen ke Asisten Manager</div>

    <div class="grid grid-cols-2 gap-5">
      <!-- Form -->
      <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="text-sm font-medium mb-4 text-amber-700">Form Input Dokumen Baru</div>

        <div v-if="errorMsg" class="bg-red-50 border border-red-200 text-red-600 text-xs px-3 py-2 rounded-lg mb-4">{{ errorMsg }}</div>
        <div v-if="successMsg" class="bg-green-50 border border-green-200 text-green-600 text-xs px-3 py-2 rounded-lg mb-4 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          {{ successMsg }}
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Nama Dokumen <span class="text-red-400">*</span></label>
            <input v-model="form.nomor_dokumen" type="text" placeholder="cth. SPK-2026/001"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
              :class="{'border-red-300': errors.nomor_dokumen}"/>
            <p v-if="errors.nomor_dokumen" class="text-xs text-red-500 mt-1">{{ errors.nomor_dokumen }}</p>
          </div>

          <div>
            <label class="block text-xs text-gray-500 mb-1">Project <span class="text-red-400">*</span></label>
            <select v-model="form.project_id"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
              :class="{'border-red-300': errors.project_id}">
              <option value="">Pilih project</option>
              <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
            <p v-if="errors.project_id" class="text-xs text-red-500 mt-1">{{ errors.project_id }}</p>
          </div>

          <div>
            <label class="block text-xs text-gray-500 mb-1">Tanggal Masuk</label>
            <input v-model="form.tanggal_masuk" type="date"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"/>
          </div>

          <div>
            <label class="block text-xs text-gray-500 mb-1">Review Deadline <span class="text-red-400">*</span></label>
            <input v-model="form.review_deadline" type="date"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
              :class="{'border-red-300': errors.review_deadline}"/>
            <p v-if="errors.review_deadline" class="text-xs text-red-500 mt-1">{{ errors.review_deadline }}</p>
            <p class="text-xs text-gray-400 mt-1">PIC akan mendapat notifikasi H-1, Hari H, dan H+1.</p>
          </div>

          <div>
            <label class="block text-xs text-gray-500 mb-1">Catatan untuk Asisten Manager</label>
            <textarea v-model="form.catatan" rows="3" placeholder="Catatan atau instruksi khusus..."
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"></textarea>
          </div>

          <!-- Upload file -->
          <div>
            <label class="block text-xs text-gray-500 mb-1">Upload File Proyek</label>
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
                <button @click.stop="selectedFile = null" class="ml-auto text-gray-400 hover:text-red-500">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Progress bar -->
        <div v-if="uploadProgress > 0 && uploadProgress < 100" class="mt-3">
          <div class="flex justify-between text-xs text-gray-500 mb-1">
            <span>Mengupload...</span><span>{{ uploadProgress }}%</span>
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
          {{ loading ? 'Menyimpan...' : 'Simpan sebagai Draft' }}
        </button>
      </div>

      <!-- Riwayat -->
      <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="text-sm font-medium mb-4">Daftar Proyek Saya</div>
        <div v-if="myDocs.length === 0" class="text-center py-10 text-gray-400 text-sm">
          Belum ada proyek
        </div>
        <div v-else class="space-y-2 max-h-[500px] overflow-y-auto">
          <div v-for="doc in myDocs" :key="doc.id"
            :class="doc.submit_status === 'submitted' ? 'border-amber-200 bg-amber-50/20' : 'border-gray-200'"
            class="p-3 border rounded-lg hover:bg-gray-50 transition">
            <div class="flex items-start gap-3">
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium">{{ doc.nomor_dokumen }}</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ doc.project?.name }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Deadline: {{ formatDate(doc.review_deadline) }}</div>
                <div v-if="doc.catatan" class="text-xs text-gray-400 mt-0.5 italic">"{{ doc.catatan }}"</div>

                <div v-if="doc.file_path" class="mt-1">
                  <a :href="'http://localhost:8000/storage/' + doc.file_path" target="_blank"
                    class="flex items-center gap-1 text-xs text-blue-600 hover:underline w-fit">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    Lihat file
                  </a>
                </div>

                <div v-if="doc.pic_id" class="mt-2 pt-2 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-500 flex-wrap">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                  PIC: <span class="font-medium text-blue-600">{{ doc.pic?.name }}</span>
                  <span class="mx-0.5">·</span>
                  Asisten: <span class="font-medium">{{ doc.atasan?.name }}</span>
                </div>
              </div>

              <div class="flex flex-col items-end gap-2 flex-shrink-0">
                <span :class="submitStatusClass(doc.submit_status)"
                  class="text-xs px-2 py-0.5 rounded-full border font-medium whitespace-nowrap">
                  {{ submitStatusLabel(doc.submit_status) }}
                </span>
                <StatusBadge v-if="doc.pic_id" :status="doc.status" />

<button v-if="doc.submit_status === 'draft'"
  @click="openSendModal(doc)"
  class="text-xs bg-amber-500 text-white px-3 py-1.5 rounded-lg hover:bg-amber-600 transition flex items-center gap-1 whitespace-nowrap">
  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
  </svg>
  Kirim ke Asisten
</button>

                <button v-if="doc.submit_status === 'draft'"
                  @click="deleteDoc(doc.id)"
                  class="text-xs text-red-400 hover:text-red-600 hover:underline transition">
                  Hapus
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal pilih asisten -->
<div v-if="showSendModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[999]">
  <div class="bg-white rounded-2xl p-6 w-80 border border-gray-200 shadow-xl">
    <div class="text-base font-semibold mb-1">Kirim ke Asisten Manager</div>
    <div class="text-xs text-gray-500 mb-4">{{ selectedDocToSend?.nomor_dokumen }}</div>

    <label class="block text-xs text-gray-500 mb-1">Pilih Asisten Manager <span class="text-red-400">*</span></label>
    <select v-model="targetAsistenId"
      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
      <option value="">Pilih Asisten Manager</option>
      <option v-for="a in asistenList" :key="a.id" :value="a.id">{{ a.name }}</option>
    </select>

    <div class="flex gap-2 justify-end mt-5">
      <button @click="showSendModal = false" class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
      <button @click="confirmSend" :disabled="submittingId === selectedDocToSend?.id"
        class="text-sm px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 disabled:opacity-50 flex items-center gap-2">
        <svg v-if="submittingId === selectedDocToSend?.id" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
        {{ submittingId === selectedDocToSend?.id ? 'Mengirim...' : 'Kirim' }}
      </button>
    </div>
  </div>
</div>

    <!-- Confirm hapus -->
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

    <!-- Toast -->
    <div v-if="toast"
      class="fixed bottom-5 right-5 text-white text-sm px-4 py-3 rounded-xl shadow-lg z-50 flex items-center gap-2"
      :class="toastType === 'error' ? 'bg-red-600' : 'bg-green-600'">
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
const projects       = ref([])
const myDocs         = ref([])
const showConfirm    = ref(false)
const deleteId       = ref(null)
const loading        = ref(false)
const submittingId   = ref(null)
const errorMsg       = ref('')
const successMsg     = ref('')
const errors         = ref({})
const toast          = ref('')
const toastType      = ref('success')
const selectedFile   = ref(null)
const isDragging     = ref(false)
const uploadProgress = ref(0)
const fileInput      = ref(null)
const showSendModal     = ref(false)
const selectedDocToSend = ref(null)
const targetAsistenId   = ref('')
const asistenList       = ref([])

const form = ref({ nomor_dokumen: '', project_id: '', tanggal_masuk: '', review_deadline: '', catatan: '' })

const fileSize = computed(() => {
  if (!selectedFile.value) return ''
  const b = selectedFile.value.size
  if (b < 1024) return b + ' B'
  if (b < 1024*1024) return (b/1024).toFixed(1) + ' KB'
  return (b/(1024*1024)).toFixed(1) + ' MB'
})

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function showToastMsg(msg, type = 'success') {
  toast.value     = msg
  toastType.value = type
  setTimeout(() => toast.value = '', 3000)
}

function submitStatusLabel(s) {
  return {
    draft:    'Draft — Belum Dikirim',
    submitted:'Menunggu Asisten Manager',
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

function onFileChange(e) {
  const file = e.target.files[0]
  if (file && file.size <= 10*1024*1024) selectedFile.value = file
  else if (file) showToastMsg('Maksimal 10MB', 'error')
}

function onDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  if (file && file.size <= 10*1024*1024) selectedFile.value = file
  else if (file) showToastMsg('Maksimal 10MB', 'error')
}

async function submit() {
  errorMsg.value = ''; successMsg.value = ''; errors.value = {}
  if (!form.value.nomor_dokumen)   { errors.value.nomor_dokumen = 'Wajib diisi'; return }
  if (!form.value.project_id)      { errors.value.project_id   = 'Wajib dipilih'; return }
  if (!form.value.review_deadline) { errors.value.review_deadline = 'Wajib diisi'; return }

  loading.value = true; uploadProgress.value = 0
  try {
    const fd = new FormData()
    Object.keys(form.value).forEach(k => { if (form.value[k]) fd.append(k, form.value[k]) })
    if (selectedFile.value) fd.append('file', selectedFile.value)

    await api.post('/documents', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: e => { uploadProgress.value = Math.round((e.loaded/e.total)*100) }
    })

    successMsg.value = `Proyek ${form.value.nomor_dokumen} berhasil disimpan! Klik "Kirim ke Asisten" untuk mengirimkan.`
    form.value = { nomor_dokumen: '', project_id: '', tanggal_masuk: '', review_deadline: '', catatan: '' }
    selectedFile.value = null; uploadProgress.value = 0
    if (fileInput.value) fileInput.value.value = ''
    loadMyDocs()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Gagal menyimpan'
  } finally {
    loading.value = false
  }
}

async function submitToAsisten(doc) {
  submittingId.value = doc.id
  try {
    await api.put(`/documents/${doc.id}`, { submit_to_manager: true })
    showToastMsg(`Proyek ${doc.nomor_dokumen} berhasil dikirim ke Asisten Manager!`)
    loadMyDocs()
  } catch {
    showToastMsg('Gagal mengirim ke Asisten Manager', 'error')
  } finally {
    submittingId.value = null
  }
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
    showToastMsg('Gagal menghapus', 'error')
  }
}

async function loadMyDocs() {
  const res    = await api.get('/documents')
  myDocs.value = res.data.filter(d => d.asisten_id === auth.user?.id)
}

function openSendModal(doc) {
  selectedDocToSend.value = doc
  targetAsistenId.value   = ''
  showSendModal.value     = true
}

async function confirmSend() {
  if (!targetAsistenId.value) {
    showToastMsg('Pilih Asisten Manager tujuan!')
    return
  }
  submittingId.value = selectedDocToSend.value.id
  try {
    await api.put(`/documents/${selectedDocToSend.value.id}`, {
      submit_to_manager: true,
      target_asisten_id: targetAsistenId.value,
    })
    showSendModal.value = false
    showToastMsg(`Proyek berhasil dikirim ke Asisten Manager!`)
    loadMyDocs()
  } catch (e) {
    console.error(e)
    const msg = e.response?.data?.message || JSON.stringify(e.response?.data?.errors) || e.message
    showToastMsg('Gagal: ' + msg, 'error')
  } finally {
    submittingId.value = null
  }
}

onMounted(async () => {
  const [pRes, uRes] = await Promise.all([api.get('/projects'), api.get('/users')])
  projects.value    = pRes.data
  asistenList.value = uRes.data.filter(u => u.role === 'asisten_manager')
  loadMyDocs()
})
</script>