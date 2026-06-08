<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <div class="text-base font-semibold">Manajemen Users</div>
      <button @click="showModal = true"
        class="text-xs bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
        + Tambah User
      </button>
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
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nama</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Email</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">WhatsApp</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Role</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(u, i) in users" :key="u.id"
            class="border-b border-gray-50 hover:bg-gray-50">
            <td class="px-4 py-3 text-gray-400 text-xs">{{ String(i+1).padStart(3,'0') }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0"
                  :class="avatarClass(u.role)">
                  {{ initials(u.name) }}
                </div>
                <span class="font-medium">{{ u.name }}</span>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-500">{{ u.email }}</td>
            <td class="px-4 py-3 text-gray-500">{{ u.whatsapp || '—' }}</td>
            <td class="px-4 py-3">
              <span :class="roleClass(u.role)"
                class="text-xs px-2.5 py-1 rounded-full font-medium inline-flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                  :class="{
                    'bg-purple-500': u.role === 'admin',
                    'bg-amber-500':  u.role === 'manager',
                    'bg-blue-500':   u.role === 'asisten_manager',
                    'bg-green-500':  u.role === 'pic',
                  }"></span>
                {{ roleLabel(u.role) }}
              </span>
            </td>
            <td class="px-4 py-3">
              <button @click="deleteUser(u.id)"
                class="inline-flex items-center gap-1 text-xs text-red-500 hover:text-red-700 hover:bg-red-50 px-2.5 py-1.5 rounded-lg transition border border-transparent hover:border-red-100">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus
              </button>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada user</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal tambah user -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-80 border border-gray-200 shadow-xl">
        <div class="text-base font-semibold mb-4">Tambah User</div>

        <div v-if="errorMsg" class="bg-red-50 border border-red-200 text-red-600 text-xs px-3 py-2 rounded-lg mb-3">
          {{ errorMsg }}
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Nama <span class="text-red-400">*</span></label>
            <input v-model="form.name" type="text" placeholder="cth. Budi Santoso"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
              :class="{'border-red-300': errors.name}"/>
            <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Email <span class="text-red-400">*</span></label>
            <input v-model="form.email" type="email" placeholder="cth. budi@email.com"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
              :class="{'border-red-300': errors.email}"/>
            <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">WhatsApp</label>
            <input v-model="form.whatsapp" type="text" placeholder="628xxxxxxxxxx"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"/>
            <p class="text-xs text-gray-400 mt-1">Format: 628xxxxxxxxxx (tanpa + atau 0 di depan)</p>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Password <span class="text-red-400">*</span></label>
            <input v-model="form.password" type="password" placeholder="min. 6 karakter"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
              :class="{'border-red-300': errors.password}"/>
            <p v-if="errors.password" class="text-xs text-red-500 mt-1">{{ errors.password }}</p>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Role <span class="text-red-400">*</span></label>
            <select v-model="form.role"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
              <option value="admin">Admin</option>
              <option value="manager">Manager</option>
              <option value="asisten_manager">Asisten Manager</option>
              <option value="pic">PIC</option>
            </select>
          </div>
        </div>

        <div class="flex gap-2 justify-end mt-5">
          <button @click="closeModal"
            class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Batal
          </button>
          <button @click="addUser" :disabled="loading"
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
            <div class="text-sm font-semibold text-gray-900">Hapus User</div>
            <div class="text-xs text-gray-500 mt-0.5">User yang dihapus tidak bisa dikembalikan.</div>
          </div>
        </div>
        <div class="flex gap-2 justify-end">
          <button @click="showConfirm = false"
            class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Batal
          </button>
          <button @click="confirmDelete"
            class="text-sm px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
            Ya, Hapus
          </button>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toastMsg"
      class="fixed bottom-5 right-5 text-white text-sm px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 z-50"
      :class="toastType === 'error' ? 'bg-red-600' : 'bg-green-600'">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path v-if="toastType !== 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
      {{ toastMsg }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../axios'

const users        = ref([])
const showModal    = ref(false)
const showConfirm  = ref(false)
const deleteId     = ref(null)
const loading      = ref(false)
const loadingTable = ref(false)
const errorMsg     = ref('')
const toastMsg     = ref('')
const toastType    = ref('success')
const errors       = ref({})
const form         = ref({ name: '', email: '', whatsapp: '', password: '', role: 'pic' })

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

function avatarClass(role) {
  return {
    admin:           'bg-purple-100 text-purple-700',
    manager:         'bg-amber-100 text-amber-700',
    asisten_manager: 'bg-blue-100 text-blue-700',
    pic:             'bg-green-100 text-green-700',
  }[role] || 'bg-gray-100 text-gray-600'
}

function roleClass(role) {
  return {
    admin:           'bg-purple-100 text-purple-700 border border-purple-200',
    manager:         'bg-amber-100 text-amber-700 border border-amber-200',
    asisten_manager: 'bg-blue-100 text-blue-700 border border-blue-200',
    pic:             'bg-green-100 text-green-700 border border-green-200',
  }[role] || 'bg-gray-100 text-gray-600 border border-gray-200'
}

function roleLabel(role) {
  return {
    admin:           'Admin',
    manager:         'Manager',
    asisten_manager: 'Asisten Manager',
    pic:             'PIC',
  }[role] || role
}

function showToast(msg, type = 'success') {
  toastMsg.value  = msg
  toastType.value = type
  setTimeout(() => toastMsg.value = '', 3000)
}

function closeModal() {
  showModal.value = false
  errorMsg.value  = ''
  errors.value    = {}
  form.value      = { name: '', email: '', whatsapp: '', password: '', role: 'pic' }
}

async function load() {
  loadingTable.value = true
  try {
    const res   = await api.get('/users')
    users.value = res.data
  } finally {
    loadingTable.value = false
  }
}

async function addUser() {
  errorMsg.value = ''
  errors.value   = {}
  if (!form.value.name)     { errors.value.name     = 'Nama wajib diisi'; return }
  if (!form.value.email)    { errors.value.email    = 'Email wajib diisi'; return }
  if (!form.value.password) { errors.value.password = 'Password wajib diisi'; return }
  if (form.value.password.length < 6) { errors.value.password = 'Password minimal 6 karakter'; return }

  loading.value = true
  try {
    await api.post('/users', form.value)
    closeModal()
    showToast('User berhasil ditambahkan!')
    load()
  } catch (e) {
    const data = e.response?.data
    if (data?.errors) {
      errors.value = {}
      Object.keys(data.errors).forEach(k => errors.value[k] = data.errors[k][0])
    } else {
      errorMsg.value = data?.message || 'Gagal menyimpan user'
    }
  } finally {
    loading.value = false
  }
}

function deleteUser(id) {
  deleteId.value    = id
  showConfirm.value = true
}

async function confirmDelete() {
  showConfirm.value = false
  try {
    await api.delete(`/users/${deleteId.value}`)
    showToast('User berhasil dihapus!')
    load()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal menghapus user', 'error')
  }
}

onMounted(load)
</script>