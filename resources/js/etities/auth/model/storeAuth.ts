import { watch, ref } from "vue"
import { defineStore } from "pinia"
import {
    // login,
    // logout,
    // fetchUserByToken,
    fetchVerifyCode,
    fetchResendCode
} from '../api/auth'

export const useAuthStore = defineStore('auth', () => {
    const token = ref<string>(localStorage.getItem('auth_token') || '') // Сохраняем токен
    const isAuth = ref<boolean>(!!token.value) // isAuth зависит от наличия токена
    // const user = ref(null)

    watch(token, (newValue: string): void => {
        if (newValue) {
            localStorage.setItem('auth_token', newValue) // Сохраняем токен
            isAuth.value = true
        } else {
            localStorage.removeItem('auth_token') // Удаляем токен
            isAuth.value = false
        }
    })

    const verifyCode = async (code: string) => {
        return await fetchVerifyCode(code)
    }
    const resendCode = async () => {
        return  await fetchResendCode()
    }

    // const loginUser = async (credentials) => {
    //     const response = await login(credentials)
    //     setToken(response.token)
    //     isAuthenticated.value = true
    //     await fetchUser()
    // }
    //
    // const logoutUser = async () => {
    //     await logout()
    //     clearToken()
    //     isAuthenticated.value = false
    //     user.value = null
    // }
    //
    // const fetchUser = async () => {
    //     if (token.value) {
    //         user.value = await fetchUserByToken(token.value)
    //     }
    // }

    return {
        token,
        isAuth,
        // user,
        // loginUser,
        // logoutUser,
        // fetchUser,

        verifyCode,
        resendCode
    }
})
