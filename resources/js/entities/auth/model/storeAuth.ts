import { watch, ref, computed } from "vue"
import { defineStore, storeToRefs } from "pinia"
import {
    fetchVerifyCode,
    fetchResendCode
} from '../api/auth'
import type { AuthResponse } from '../../../app/types/auth'
import { useUsersStore } from "../../user"
// import api from "../../../app/api/api"

export const useAuthStore = defineStore('auth', () => {
    // State
    const token = ref<string>(localStorage.getItem('auth_token') || '')
    const currentVerifyLogin = ref<string>(localStorage.getItem('auth_login') || '')
    const userStore = useUsersStore()
    // const isAuthCheckInProgress = ref(false)
    // const user = ref<User | null>(null)

    // Computed
    const isAuth = computed(() => !!token.value)

    // Watchers
    watch(token, async (newToken: string) => {
        if (newToken) {
            localStorage.setItem('auth_token', newToken)
            // await userStore.getUserByToken(newToken)
        } else {
            localStorage.removeItem('auth_token')
            // user.value = null // Очищаем пользователя при logout
        }
    })

    watch(currentVerifyLogin, (newLogin: string) => {
        if (newLogin) {
            localStorage.setItem('auth_login', newLogin)
        } else {
            localStorage.removeItem('auth_login')
        }
    })

    // Actions
    const setAuthData = (data: { token: string; login: string }) => {
        token.value = data.token
        currentVerifyLogin.value = data.login
    }

    const clearAuthData = () => {
        token.value = ''
        currentVerifyLogin.value = ''
        // isAuthCheckInProgress.value = false
    }

    const verifyCode = async (code: string): Promise<AuthResponse> => {
        try {
            const response = await fetchVerifyCode(code, currentVerifyLogin.value)
            setAuthData({
                token: response.token,
                login: currentVerifyLogin.value
            })
            return response
        } catch (error) {
            // clearAuthData()
            throw error
        }
    }

    const resendCode = async (): Promise<void> => {
        if (!currentVerifyLogin.value) {
            throw new Error('No login provided for verification')
        }
        await fetchResendCode(currentVerifyLogin.value)
    }

    // const checkAuth = async (): Promise<boolean> => {
    //     if (isAuthCheckInProgress.value) return isAuth.value
    //     isAuthCheckInProgress.value = true
    //
    //     try {
    //         await api.get('/auth/check')
    //         return isAuth.value
    //     } catch (error) {
    //         clearAuthData()
    //         return false
    //     } finally {
    //         isAuthCheckInProgress.value = false
    //     }
    // }

    return {
        token,
        isAuth,
        currentVerifyLogin,
        // isAuthCheckInProgress,
        // user,
        verifyCode,
        resendCode,
        setAuthData,
        clearAuthData,
        // checkAuth
    }
})
