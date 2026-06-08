import { defineStore } from 'pinia'
import api from '../axios'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token') || null,
        user:  JSON.parse(localStorage.getItem('user') || 'null'),
    }),
    actions: {
        async login(email, password) {
            const res = await api.post('/login', { email, password })
            this.token = res.data.token
            this.user  = res.data.user
            localStorage.setItem('token', this.token)
            localStorage.setItem('user',  JSON.stringify(this.user))
        },
        logout() {
            api.post('/logout').finally(() => {
                this.token = null
                this.user  = null
                localStorage.removeItem('token')
                localStorage.removeItem('user')
                window.location.href = '/login'
            })
        }
    }
})