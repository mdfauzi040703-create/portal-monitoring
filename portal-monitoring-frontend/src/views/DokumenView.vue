<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-800">Kelola Dokumen</h2>
        <p class="text-gray-500 text-sm mt-1">Input dan kelola data dokumen review</p>
      </div>
      <button
        @click="openForm()"
        class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
      >
        + Tambah Dokumen
      </button>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 mb-4 flex gap-3 flex-wrap">
      <input
        v-model="search"
        placeholder="Cari nomor atau judul dokumen..."
        class="flex-1 min-w-48 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300"
      />
      <select v-model="filterStatus" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
        <option value="">Semua Status</option>
        <option value="menunggu">Menunggu</option>
        <option value="review">Dalam Review</option>
        <option value="selesai">Selesai</option>
        <option value="terlambat">Terlambat</option>
      </select>
    </div>

    <!-- Tabel -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-gray-50 text-gray-500 text-left">
            <th class="px-5 py-3 font-medium">No. Dokumen</th>
            <th class="px-5 py-3 font-medium">Judul</th>
            <th class="px-5 py-3 font-medium">Tgl Diterima</th>
            <th class="px-5 py-3 font-medium">Deadline</th>
            <th class="px-5 py-3 font-medium">Tgl Kembali</th>
            <th class="px-5 py-3 font-medium">PIC</th>
            <th class="px-5 py-3 font-medium">Status</th>
            <th class="px-5 py-3 font-medium">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="docStore.loading">
            <td colspan="8" class="px-5 py-8 text-center text-gray-400">Memuat data...</td>
          </tr>
          <tr v-else-if="!filteredDocs.length">
            <td colspan="8" class="px-5 py-8 text-center text-gray-400">Tidak ada dokumen ditemukan</td>
          </tr>
          <tr v-for="doc in filteredDocs" :key="doc.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ doc.nomor_dokumen }}</td>
            <td class="px-5 py-3 text-gray-800 max-w-xs truncate">{{ doc.judul }}</td>
            <td class="px-5 py-3 text-gray-600">{{ formatDate(doc.tanggal_diterima) }}</td>
            <td class="px-5 py-3">
              <span :class="deadlineClass(doc.tanggal_deadline)">{{ formatDate(doc.tanggal_deadline) }}</span>
            </td>
            <td class="px-5 py-3 text-gray-600">{{ doc.tanggal_kembali ? formatDate(doc.tanggal_kembali) : '-' }}</td>
            <td class="px-5 py-3 text-gray-600">{{ doc.pic?.name ?? '-' }}</td>
            <td class="px-5 py-3"><StatusBadge :status="doc.status" /></td>
            <td class="px-5 py-3">
              <div class="flex gap-2">
                <button @click="openForm(doc)" class="text-blue-500 hover:text-blue-700 text-xs font-medium">Edit</button>
                <button @click="hapus(doc)" class="text-red-400 hover:text-red-600 text-xs font-medium">Hapus</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-bold text-gray-800">{{ editTarget ? 'Edit Dokumen' : 'Tambah Dokumen' }}</h3>
          <button @click="showForm = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <form @submit.prevent="submitForm" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Dokumen</label>
            <input v-model="form.nomor_dokumen" required class="input-field" placeholder="DOC-2024-001" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Dokumen</label>
            <input v-model="form.judul" required class="input-field" placeholder="Judul dokumen..." />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Diterima</label>
              <input v-model="form.tanggal_diterima" type="date" required class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Deadline</label>
              <input v-model="form.tanggal_deadline" type="date" required class="input-field" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kembali</label>
            <input v-model="form.tanggal_kembali" type="date" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">PIC</label>
            <select v-model="form.pic_id" class="input-field">
              <option value="">-- Pilih PIC --</option>
              <option v-for="u in picList" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="form.status" class="input-field">
              <option value="menunggu">Menunggu</option>
              <option value="review">Dalam Review</option>
              <option value="selesai">Selesai</option>
            </select>
          </div>
          <p v-if="formError" class="text-red-500 text-sm bg-red-50 px-3 py-2 rounded-lg">{{ formError }}</p>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="showForm = false" class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">Batal</button>
            <button type="submit" :disabled="submitting" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white rounded-lg text-sm font-medium">
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useDocumentStore } from '@/stores/document'
import { userApi } from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'

const docStore = useDocumentStore()
const picList = ref([])
const search = ref('')
const filterStatus = ref('')
const showForm = ref(false)
const editTarget = ref(null)
const submitting = ref(false)
const formError = ref('')

const defaultForm = () => ({
  nomor_dokumen: '', judul: '', tanggal_diterima: '', tanggal_deadline: '',
  tanggal_kembali: '', pic_id: '', status: 'menunggu',
})
const form = ref(defaultForm())

const filteredDocs = computed(() => {
  return docStore.documents.filter(d => {
    const matchSearch = !search.value ||
      d.nomor_dokumen?.toLowerCase().includes(search.value.toLowerCase()) ||
      d.judul?.toLowerCase().includes(search.value.toLowerCase())
    const matchStatus = !filterStatus.value || d.status === filterStatus.value
    return matchSearch && matchStatus
  })
})

onMounted(async () => {
  await docStore.fetchAll()
  const res = await userApi.list()
  picList.value = (res.data.data ?? res.data).filter(u => u.role === 'pic')
})

function openForm(doc = null) {
  editTarget.value = doc
  form.value = doc ? { ...doc, pic_id: doc.pic?.id ?? '' } : defaultForm()
  formError.value = ''
  showForm.value = true
}

async function submitForm() {
  submitting.value = true
  formError.value = ''
  try {
    if (editTarget.value) await docStore.update(editTarget.value.id, form.value)
    else await docStore.create(form.value)
    showForm.value = false
  } catch (e) {
    formError.value = e.response?.data?.message || 'Gagal menyimpan dokumen'
  } finally {
    submitting.value = false
  }
}

async function hapus(doc) {
  if (!confirm(`Hapus dokumen "${doc.judul}"?`)) return
  await docStore.remove(doc.id)
}

function formatDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function deadlineClass(dateStr) {
  if (!dateStr) return 'text-gray-500'
  const diff = Math.ceil((new Date(dateStr) - new Date()) / 86400000)
  if (diff < 0) return 'text-red-600 font-semibold'
  if (diff <= 1) return 'text-orange-500 font-semibold'
  return 'text-gray-700'
}
</script>

<style scoped>
.input-field {
  @apply w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300;
}
</style>
