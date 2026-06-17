import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from './stores/auth'

const routes = [
  { path: '/login', component: () => import('./views/Login.vue'), meta: { guest: true } },
// MANAGER
{
  path: '/manager',
  component: () => import('./views/layouts/ManagerLayout.vue'),
  meta: { requiresAuth: true, role: 'manager' },
  children: [
    { path: '',              component: () => import('./views/manager/Dashboard.vue') },
    { path: 'input-proyek', component: () => import('./views/manager/InputProyek.vue') },
    { path: 'daftar-pic',   component: () => import('./views/manager/DaftarPIC.vue') },
    { path: 'log',          component: () => import('./views/manager/LogNotifikasi.vue') },
    { path: 'dokumen',      component: () => import('./views/manager/Dokumen.vue') },
  ]
},

// ASISTEN MANAGER
{
  path: '/asisten',
  component: () => import('./views/layouts/AsistenLayout.vue'),
  meta: { requiresAuth: true, role: 'asisten_manager' },
  children: [
    { path: '',              component: () => import('./views/asisten/Dashboard.vue') },
    { path: 'proyek-masuk', component: () => import('./views/asisten/ProyekMasuk.vue') },
    { path: 'dokumen',      component: () => import('./views/asisten/Dokumen.vue') },
    { path: 'log',          component: () => import('./views/asisten/LogNotifikasi.vue') },
    { path: 'users',        component: () => import('./views/asisten/Users.vue') },
  ]
},

  // ADMIN
  {
    path: '/admin',
    component: () => import('./views/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: '',               component: () => import('./views/admin/Dashboard.vue') },
      { path: 'aktivitas-pic',  component: () => import('./views/admin/AktivitasPIC.vue') },
      { path: 'log',            component: () => import('./views/admin/LogNotifikasi.vue') },
      { path: 'dokumen',        component: () => import('./views/admin/Dokumen.vue') },
      { path: 'users',          component: () => import('./views/admin/Users.vue') },
    ]
  },

  // PIC
  {
    path: '/pic',
    component: () => import('./views/layouts/PICLayout.vue'),
    meta: { requiresAuth: true, role: 'pic' },
    children: [
      { path: '',             component: () => import('./views/pic/Dashboard.vue') },
      { path: 'log',          component: () => import('./views/pic/LogNotifikasi.vue') },
      { path: 'proyek-masuk', component: () => import('./views/pic/ProyekMasuk.vue') },
      { path: 'input-proyek', component: () => import('./views/pic/InputProyek.vue') },
    ]
  },

  { path: '/', redirect: () => {
    const auth = useAuthStore()
    if (!auth.token) return '/login'
    if (auth.user?.role === 'admin')            return '/admin'
    if (auth.user?.role === 'manager')          return '/manager'
    if (auth.user?.role === 'asisten_manager')  return '/asisten'
    if (auth.user?.role === 'pic')              return '/pic'
    return '/login'
  }},

  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.guest && auth.token) {
    const role = auth.user?.role
    if (role === 'admin')           return next('/admin')
    if (role === 'manager')         return next('/manager')
    if (role === 'asisten_manager') return next('/asisten')
    if (role === 'pic')             return next('/pic')
  }

  if (to.meta.requiresAuth && !auth.token) return next('/login')
  if (to.meta.role && auth.user?.role !== to.meta.role) return next('/')
  next()
})

export default router