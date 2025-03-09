<script setup>
import api from "@/app/api/api.js"
import { useRouter } from 'vue-router'
import { useAuthStore } from "@/etities/auth/model/index.js"

const router = useRouter()
const authStore = useAuthStore()

const logout = async () => {
    try {
        await api.post('/logout', {}, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
            },
        })
        authStore.token = ''
        // localStorage.removeItem('auth_token')
        await router.push('/')
    } catch (error) {
        console.error(error)
    }
}
</script>

<template>
    <div class="px-[10%] py-10">
        <div>Logout</div>
        <button @click="logout" class="btn">do</button>
    </div>
</template>
