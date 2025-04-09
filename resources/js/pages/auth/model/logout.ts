import api from "@/app/api/api.ts"
import { useRouter } from 'vue-router'
import { ref } from "vue"
import { useAuthStore } from "../../../etities/auth"


export const useLogout = () => {
    const router = useRouter()
    const authStore = useAuthStore()
    const isLoadingLogout = ref(false)

    const doLogout = async () => {
        isLoadingLogout.value = true
        try {
            await api.post('/logout', {}, {
                headers: {
                    Authorization: `Bearer ${authStore.token}`,
                },
            })
            authStore.clearAuthData()
            await router.push('/')
        } catch (error) {
            console.error(error)
        } finally {
            isLoadingLogout.value = false
        }
    }

    return {
        isLoadingLogout,
        doLogout
    }
}
