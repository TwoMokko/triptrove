import api from "@/app/api/api.ts"
import { useRouter } from 'vue-router'
import { ref } from "vue"
import { useAuthStore } from "../../../entities/auth"
import { useUsersStore } from "../../../entities/user"

export const useLogout = () => {
    const router = useRouter()
    const authStore = useAuthStore()
    const usersStore = useUsersStore()
    const isLoadingLogout = ref(false)
    const isLoggingOut = ref(false) // Флаг защиты от повторных вызовов

    const doLogout = async () => {
        if (isLoggingOut.value) return
        isLoggingOut.value = true
        isLoadingLogout.value = true

        const token = authStore.token

        // Сначала очищаем данные, независимо от результата запроса
        authStore.clearAuthData()
        usersStore.resetCurrentUser()
        localStorage.removeItem('auth_token')

        try {
            if (token) {
                await api.post('/auth/logout', {}, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                    __isLogoutRequest: true
                }).catch(() => {}) // Игнорируем ошибки logout-запроса
            }
        } finally {
            isLoadingLogout.value = false
            isLoggingOut.value = false
            // Используем replace вместо push, чтобы избежать навигационных циклов
            router.replace('/login').catch(() => {})
        }
    }

    return {
        isLoadingLogout,
        doLogout
    }
}
