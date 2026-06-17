<template>
  <div>
    <div class="text-base font-semibold mb-2">Proyek Masuk dari Manager</div>
    <div class="text-xs text-gray-500 mb-5">Pilih PIC untuk mengerjakan setiap proyek</div>

    <!-- Tab -->
    <div class="flex gap-2 mb-4">
      <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value"
        :class="activeTab === tab.value ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-500 hover:bg-gray-50'"
        class="text-xs px-3 py-1.5 rounded-lg transition">
        {{ tab.label }}
        <span class="ml-1 font-semibold">{{ tabCount(tab.value) }}</span>
      </button>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-16">
      <div class="flex items-center gap-3 text-gray-400">
        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
        <span class="text-sm">Memuat proyek...</span>
      </div>
    </div>

    <div v-else-if="filtered.length === 0"
      class="flex flex-col items-center justify-center py-16 bg-white border border-dashed border-gray-200 rounded-2xl text-gray-400">
      <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
      </svg>
      <div class="text-sm font-medium">Tidak ada proyek</div>
    </div>

    <div v-else class="space-y-3">
      <div v-for="doc in filtered" :key="doc.id"
        :class="doc.submit_status === 'submitted' ? 'border-amber-200 bg-amber-50/20' : 'border-gray-200'"
        class="bg-white border rounded-xl p-4 hover:shadow-sm transition">
        <div class="flex items-start gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-2">
              <span class="text-sm font-semibold text-gray-900">{{ doc.nomor_dokumen }}</span>
              <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                {{ doc.project?.name }}
              </span>
              <span :class="submitStatusClass(doc.submit_status)" class="text-xs px-2 py-0.5 rounded-full border font-medium">
                {{ submitStatusLabel(doc.submit_status) }}
              </span>
            </div>

            <div class="flex items-center gap-4 flex-wrap text-xs text-gray-500 mb-2">
              <span class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Dari: <span class="font-medium text-gray-700">{{ doc.asisten?.name || '—' }}</span>
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Deadline: <span class="font-medium" :class="deadlineColor(doc)">{{ formatDate(doc.review_deadline) }}</span>
              </span>
            </div>

            <div v-if="doc.catatan"
              class="text-xs text-gray-500 italic bg-gray-50 border border-gray-100 rounded-lg px-3 py-2 mb-2">
              "{{ doc.catatan }}"
            </div>

            <div v-if="doc.file_path" class="mb-2">
              <a :href="'http://localhost:8000/storage/' + doc.file_path" target="_blank"
                class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 hover:underline bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh / Lihat File Proyek
              </a>
            </div>
            <div v-else class="text-xs text-gray-300 mb-2 italic">Tidak ada file terlampir</div>

            <div v-if="doc.submit_status === 'assigned' || doc.submit_status === 'selesai'"
              class="flex items-center gap-3 text-xs pt-2 border-t border-gray-100 flex-wrap">
              <span class="flex items-center gap-1 text-gray-500">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                PIC: <span class="font-medium text-blue-600 ml-0.5">{{ doc.pic?.name }}</span>
              </span>
              <StatusBadge :status="doc.status" />
            </div>
          </div>

          <div class="flex flex-col gap-2 flex-shrink-0">
            <button v-if="doc.submit_status === 'submitted'"
              @click="openAssign(doc)"
              class="bg-amber-500 text-white text-xs px-4 py-2 rounded-lg hover:bg-amber-600 transition font-medium">
              Assign ke PIC
            </button>
            <button v-else-if="doc.submit_status === 'assigned'"
              @click="openAssign(doc)"
              class="border border-amber-300 text-amber-700 bg-amber-50 text-xs px-4 py-2 rounded-lg hover:bg-amber-100 transition">
              Ganti PIC
            </button>
            <span v-else-if="doc.submit_status === 'selesai'"
              class="text-xs text-green-600 font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Selesai
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal assign PIC -->
    <div v-if="showAssign" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-96 border border-gray-200 shadow-xl">
        <div class="text-base font-semibold mb-1">Assign Proyek ke PIC</div>
        <div class="text-xs text-gray-500 mb-1">
          <span class="font-medium text-gray-700">{{ selectedDoc?.nomor_dokumen }}</span>
          — {{ selectedDoc?.project?.name }}
        </div>
        <div class="text-xs text-gray-400 mb-4">
          Deadline (dari Manager): <span class="font-medium text-gray-600">{{ formatDate(selectedDoc?.review_deadline) }}</span>
        </div>

        <div>
          <label class="block text-xs text-gray-500 mb-1">Pilih PIC <span class="text-red-400">*</span></label>
          <select v-model="assignForm.pic_id"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option value="">Pilih PIC</option>
            <option v-for="u in pics" :key="u.id" :value="u.id">
              {{ u.name }} — {{ picDocCount(u.id) }} dokumen aktif
            </option>
          </select>
        </div>

        <div class="flex gap-2 justify-end mt-5">
          <button @click="showAssign = false"
            class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button @click="submitAssign" :disabled="loadingAssign"
            class="text-sm px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 disabled:opacity-50 flex items-center gap-2">
            <svg v-if="loadingAssign" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            {{ loadingAssign ? 'Menyimpan...' : 'Assign ke PIC' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast"
      class="fixed bottom-5 right-5 bg-green-600 text-white text-sm px-4 py-3 rounded-xl shadow-lg z-50 flex items-center gap-2">
      {{ toast }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../axios'
import StatusBadge from '../../components/StatusBadge.vue'

const docs          = ref([])
const pics          = ref([])
const allDocs       = ref([])
const showAssign    = ref(false)
const selectedDoc   = ref(null)
const loadingAssign = ref(false)
const loading       = ref(false)
const toast         = ref('')
const activeTab     = ref('submitted')

const assignForm = ref({ pic_id: '' })

const tabs = [
  { label: 'Menunggu Assign', value: 'submitted' },
  { label: 'Sudah Diassign',  value: 'assigned' },
  { label: 'Selesai',         value: 'selesai' },
  { label: 'Semua',           value: 'semua' },
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

function deadlineColor(doc) {
  if (doc.status === 'alert')         return 'text-red-600'
  if (doc.status === 'early_warning') return 'text-amber-600'
  return 'text-gray-700'
}

function showToastMsg(msg) {
  toast.value = msg
  setTimeout(() => toast.value = '', 3000)
}

function submitStatusLabel(s) {
  return { draft:'Draft', submitted:'Menunggu Assign', assigned:'Sedang Dikerjakan', selesai:'Selesai' }[s] || s
}

function submitStatusClass(s) {
  return {
    draft:    'bg-gray-50 text-gray-500 border-gray-200',
    submitted:'bg-amber-50 text-amber-700 border-amber-200',
    assigned: 'bg-blue-50 text-blue-700 border-blue-200',
    selesai:  'bg-green-50 text-green-700 border-green-200',
  }[s] || 'bg-gray-50 text-gray-500 border-gray-200'
}

function picDocCount(picId) {
  return allDocs.value.filter(d => d.pic_id === picId && !d.return_actual_date).length
}

function openAssign(doc) {
  selectedDoc.value = doc
  assignForm.value  = { pic_id: doc.pic_id || '' }
  showAssign.value  = true
}

async function submitAssign() {
  if (!assignForm.value.pic_id) {
    showToastMsg('PIC wajib dipilih!')
    return
  }
  loadingAssign.value = true
  try {
    await api.put(`/documents/${selectedDoc.value.id}`, {
      assign_to_pic: true,
      pic_id:        assignForm.value.pic_id,
    })
    showAssign.value = false
    showToastMsg('Proyek berhasil diassign ke PIC!')
    load()
  } catch (e) {
    showToastMsg(e.response?.data?.message || 'Gagal assign proyek')
  } finally {
    loadingAssign.value = false
  }
}

async function load() {
  loading.value = true
  try {
    const [dRes, uRes] = await Promise.all([api.get('/documents'), api.get('/users')])
    docs.value    = dRes.data.filter(d => ['submitted','assigned','selesai','draft'].includes(d.submit_status))
    pics.value    = uRes.data.filter(u => u.role === 'pic')
    allDocs.value = dRes.data
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>