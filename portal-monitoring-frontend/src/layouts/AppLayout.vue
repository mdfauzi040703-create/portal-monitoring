<template>
  <div class="min-h-screen flex bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
      <div class="px-6 py-5 border-b border-gray-100">
        <h1 class="text-lg font-bold text-blue-700">Portal Monitoring</h1>
        <p class="text-xs text-gray-400 mt-0.5">Dokumen Review</p>
      </div>

      <nav class="flex-1 px-3 py-4 space-y-1">
        <RouterLink
          v-for="item in menu"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
          :class="$route.path === item.to
            ? 'bg-blue-50 text-blue-700'
            : 'text-gray-600 hover:bg-gray-100'"
        >
          <span class="text-lg">{{ item.icon }}</span>
          {{ item.label }}
        </RouterLink>
      </nav>

      <!-- User info + logout -->
      <div class="px-4 py-4 border-t border-gray-100">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm">
            {{ userInitial }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate">{{ auth.user?.name }}</p>
            <p class="text-xs text-gray-400 capitalize">{{ auth.role }}</p>
          </div>
        </div>
        <button
          @click="handleLogout"
          class="w-full text-left px-3 py-2 text-sm text-red-500 hover:bg-red-50 rounded-lg transition-colors"
        >
          Keluar
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 overflow-auto">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const userInitial = computed(() => auth.user?.name?.charAt(0).toUpperCase() || '?')

const allMenu = [
  { to: '/dashboard',    label: 'Dashboard',       icon: '📊', roles: ['manager'] },
  { to: '/dokumen',      label: 'Kelola Dokumen',   icon: '📁', roles: ['asisten', 'manager'] },
  { to: '/dokumen-saya', label: 'Dokumen Saya',     icon: '📋', roles: ['pic'] },
  { to: '/notifikasi',   label: 'Log Notifikasi',   icon: '🔔', roles: ['manager', 'asisten', 'pic'] },
  { to: '/pengguna',     label: 'Manajemen User',   icon: '👥', roles: ['manager'] },
]

const menu = computed(() => allMenu.filter(m => m.roles.includes(auth.role)))

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>
