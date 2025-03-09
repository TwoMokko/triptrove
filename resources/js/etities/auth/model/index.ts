import { watch, ref } from "vue";
import { defineStore } from "pinia";

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('auth_token') || '') // Сохраняем токен
    const isAuth = ref(!!token.value) // isAuth зависит от наличия токена

    watch(token, (newValue) => {
        if (newValue) {
            localStorage.setItem('auth_token', newValue) // Сохраняем токен
            isAuth.value = true
        } else {
            localStorage.removeItem('auth_token') // Удаляем токен
            isAuth.value = false
        }
    })

    return {
        token,
        isAuth,
    }
})
