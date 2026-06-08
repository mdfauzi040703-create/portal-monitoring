import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/DashboardView.vue'),
        meta: { roles: ['manager'] },
      },
      {
        path: 'dokumen',
        name: 'Dokumen',
        component: () => import('@/views/DokumenView.vue'),
        meta: { roles: ['asisten', 'manager'] },
      },
      {
        path: 'dokumen-saya',
        name: 'DokumenSaya',
        component: () => import('@/views/DokumenSayaView.vue'),
        meta: { roles: ['pic'] },
      },
      {
        path: 'notifikasi',
        name: 'Notifikasi',
        component: () => import('@/views/NotifikasiView.vue'),
        meta: { roles: ['manager', 'asisten', 'pic'] },
      },
      {
        path: 'pengguna',
        name: 'Pengguna',
        component: () => import('@/views/PenggunaView.vue'),
        meta: { roles: ['manager'] },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next('/login')
  }

  if (to.meta.guest && auth.isLoggedIn) {
    return next('/')
  }

  if (to.meta.roles && !to.meta.roles.includes(auth.role)) {
    // Redirect ke halaman sesuai role
    if (auth.isManager) return next('/dashboard')
    if (auth.isAsisten) return next('/dokumen')
    if (auth.isPic) return next('/dokumen-saya')
    return next('/login')
  }

  next()
})

export default router
