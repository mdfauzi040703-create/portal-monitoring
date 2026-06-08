<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">

      <div class="text-center mb-8">
        <div class="text-3xl font-bold text-gray-900 mb-1">Portal<span class="text-amber-500">Monitor</span></div>
        <div class="text-sm text-gray-400">Approval Drawing Monitoring SBU</div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- Pilih Role -->
        <div class="grid grid-cols-4 border-b border-gray-100">
          <button v-for="r in roles" :key="r.value"
            @click="selectedRole = r.value"
            :class="selectedRole === r.value
              ? 'border-b-2 border-amber-500 text-amber-700 bg-amber-50'
              : 'text-gray-400 hover:bg-gray-50 border-b-2 border-transparent'"
            class="flex flex-col items-center gap-1.5 py-3 px-1 transition text-xs font-medium">
            <div :class="selectedRole === r.value ? 'bg-amber-100' : 'bg-gray-100'"
              class="w-8 h-8 rounded-full flex items-center justify-center transition">
              <svg v-if="r.value === 'admin'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
              <svg v-else-if="r.value === 'manager'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <svg v-else-if="r.value === 'asisten_manager'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </div>
            {{ r.label }}
          </button>
        </div>

        <!-- Info role -->
        <div :class="roleInfo[selectedRole].bg" class="px-5 py-3 border-b border-gray-100">
          <div class="text-xs font-medium mb-0.5" :class="roleInfo[selectedRole].text">
            {{ roleInfo[selectedRole].title }}
          </div>
          <div class="text-xs text-gray-500 leading-relaxed">{{ roleInfo[selectedRole].desc }}</div>
        </div>

        <!-- Form -->
        <div class="p-6">
          <form @submit.prevent="doLogin">
            <div class="mb-4">
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Email</label>
              <input v-model="email" type="email" required :placeholder="roleInfo[selectedRole].placeholder"
                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"/>
            </div>
            <div class="mb-5">
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Password</label>
              <div class="relative">
                <input v-model="password" :type="showPass ? 'text' : 'password'" required placeholder="••••••••"
                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition pr-10"/>
                <button type="button" @click="showPass = !showPass"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <svg v-if="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/>
                  </svg>
                </button>
              </div>
            </div>

            <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 text-xs px-3 py-2 rounded-lg mb-4 flex items-center gap-2">
              <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              {{ error }}
            </div>

            <button type="submit" :disabled="loading"
              class="w-full bg-gray-900 text-white rounded-xl py-2.5 text-sm font-medium hover:bg-gray-700 transition disabled:opacity-50 flex items-center justify-center gap-2">
              <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
              {{ loading ? 'Masuk...' : 'Masuk sebagai ' + roleInfo[selectedRole].label }}
            </button>
          </form>
        </div>
      </div>

      <div class="text-center mt-4 text-xs text-gray-400">
        Portal Monitoring © {{ new Date().getFullYear() }} — SBU
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth         = useAuthStore()
const router       = useRouter()
const email        = ref('')
const password     = ref('')
const error        = ref('')
const loading      = ref(false)
const showPass     = ref(false)
const selectedRole = ref('manager')

const roles = [
  { value: 'manager',         label: 'Manager' },
  { value: 'asisten_manager', label: 'Asisten Mgr' },
  { value: 'pic',             label: 'PIC' },
  { value: 'admin',           label: 'Admin' },
]

const roleInfo = {
  admin: {
    title:       'Login sebagai Admin',
    desc:        'Kelola sistem, pantau semua proyek dan aktivitas portal.',
    bg:          'bg-purple-50',
    text:        'text-purple-700',
    placeholder: 'admin@portal.com',
  },
  manager: {
    title:       'Login sebagai Manager',
    desc:        'Terima proyek dari Asisten Manager, assign ke PIC, pantau progress.',
    bg:          'bg-amber-50',
    text:        'text-amber-700',
    placeholder: 'manager@portal.com',
  },
  asisten_manager: {
    title:       'Login sebagai Asisten Manager',
    desc:        'Input proyek baru beserta file, kirim ke Manager untuk diassign ke PIC.',
    bg:          'bg-blue-50',
    text:        'text-blue-700',
    placeholder: 'asisten@portal.com',
  },
  pic: {
    title:       'Login sebagai PIC',
    desc:        'Lihat proyek yang ditugaskan Manager, kerjakan dan kirim hasilnya.',
    bg:          'bg-green-50',
    text:        'text-green-700',
    placeholder: 'pic@portal.com',
  },
}

async function doLogin() {
  loading.value = true
  error.value   = ''
  try {
    await auth.login(email.value, password.value)
    if (auth.user.role !== selectedRole.value) {
      auth.logout()
      error.value = `Akun ini bukan ${roleInfo[selectedRole.value].label}. Pilih role yang sesuai.`
      return
    }
    router.push('/')
  } catch {
    error.value = 'Email atau password salah'
  } finally {
    loading.value = false
  }
}
</script>