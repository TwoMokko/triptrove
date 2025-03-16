import api from "@/app/api/api.ts"
import { useRouter } from 'vue-router'
import { useAuthStore } from "@/etities/auth"
import { ref } from "vue";

export const logout = () => {
    const router = useRouter()
    const authStore = useAuthStore()

    const isLoadingLogout = ref<boolean>(false)

   const doLogout = async () => {
        isLoadingLogout.value = true
       try {
           await api.post('/logout', {}, {
               headers: {
                   Authorization: `Bearer ${authStore.token}`,
               },
           })
           authStore.token = ''
           // localStorage.removeItem('auth_token')
           await router.push('/')
           isLoadingLogout.value = false
       } catch (error) {
           console.error(error)
       }
   }

    return {
        isLoadingLogout,
        doLogout
    }
}
