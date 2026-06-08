<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h2>
        <p class="text-gray-500 text-sm mt-1">Kelola akun Manager, Asisten, dan PIC</p>
      </div>
      <button @click="openForm()" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        + Tambah Pengguna
      </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-gray-50 text-gray-500 text-left">
            <th class="px-5 py-3 font-medium">Nama</th>
            <th class="px-5 py-3 font-medium">Email</th>
            <th class="px-5 py-3 font-medium">Role</th>
            <th class="px-5 py-3 font-medium">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="loading">
            <td colspan="4" class="px-5 py-8 text-center text-gray-400">Memuat data...</td>
          </tr>
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm">
                  {{ user.name?.charAt(0).toUpperCase() }}
                </div>
                <span class="text-gray-800 font-medium">{{ user.name }}</span>
              </div>
            </td>
            <td class="px-5 py-3 text-gray-600">{{ user.email }}</td>
            <td class="px-5 py-3">
              <span :class="roleClass(user.role)" class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ user.role }}
              </span>
            </td>
            <td class="px-5 py-3">
              <div class="flex gap-2">
                <button @click="openForm(user)" class="text-blue-500 hover:text-blue-700 text-xs font-medium">Edit</button>
                <button @click="hapus(user)" class="text-red-400 hover:text-red-600 text-xs font-medium">Hapus</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-bold text-gray-800">{{ editTarget ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h3>
          <button @click="showForm = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <form @submit.prevent="submitForm" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input v-model="form.name" required class="input-field" placeholder="Nama lengkap" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="form.email" type="email" required class="input-field" placeholder="email@perusahaan.com" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select v-model="form.role" class="input-field">
              <option value="manager">Manager</option>
              <option value="asisten">Asisten</option>
              <option value="pic">PIC</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Password {{ editTarget ? '(kosongkan jika tidak diubah)' : '' }}
            </label>
            <input v-model="form.password" type="password" :required="!editTarget" class="input-field" placeholder="••••••••" />
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
import { ref, onMounted } from 'vue'
import { userApi } from '@/services/api'

const users = ref([])
const loading = ref(true)
const showForm = ref(false)
const editTarget = ref(null)
const submitting = ref(false)
const formError = ref('')

const defaultForm = () => ({ name: '', email: '', role: 'pic', password: '' })
const form = ref(defaultForm())

onMounted(async () => {
  await loadUsers()
})

async function loadUsers() {
  loading.value = true
  try {
    const res = await userApi.list()
    users.value = res.data.data ?? res.data
  } finally {
    loading.value = false
  }
}

function openForm(user = null) {
  editTarget.value = user
  form.value = user ? { ...user, password: '' } : defaultForm()
  formError.value = ''
  showForm.value = true
}

async function submitForm() {
  submitting.value = true
  formError.value = ''
  try {
    const payload = { ...form.value }
    if (!payload.password) delete payload.password
    if (editTarget.value) await userApi.update(editTarget.value.id, payload)
    else await userApi.create(payload)
    showForm.value = false
    await loadUsers()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Gagal menyimpan data'
  } finally {
    submitting.value = false
  }
}

async function hapus(user) {
  if (!confirm(`Hapus pengguna "${user.name}"?`)) return
  await userApi.delete(user.id)
  await loadUsers()
}

function roleClass(role) {
  if (role === 'manager') return 'bg-purple-100 text-purple-700'
  if (role === 'asisten') return 'bg-blue-100 text-blue-700'
  return 'bg-green-100 text-green-700'
}
</script>

<style scoped>
.input-field {
  @apply w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300;
}
</style>
