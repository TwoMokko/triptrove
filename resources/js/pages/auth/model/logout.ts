import api from "@/app/api/api.ts"
import { useRouter } from 'vue-router'
import { ref } from "vue"
import { useAuthStore } from "../../../etities/auth"
import { useUsersStore } from "../../../etities/user"


export const useLogout = () => {
    const router = useRouter()
    const authStore = useAuthStore()
    const usersStore = useUsersStore()
    const isLoadingLogout = ref(false)

    const doLogout = async () => {
        isLoadingLogout.value = true
        try {
            await api.post('/auth/logout', {}, {
                headers: {
                    Authorization: `Bearer ${authStore.token}`,
                },
            })
            authStore.clearAuthData()
            usersStore.resetCurrentUser()

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
