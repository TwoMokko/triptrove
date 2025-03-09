<script setup lang="ts">
import { ref } from "vue"
import api from "@/app/api/api"
import { useRouter } from "vue-router"
import { useAuthStore } from "@/etities/auth/model"

interface formDataType {
    email: string,
    password: string
}

const form = ref<formDataType>({
    email: '',
    password: ''
})

const router = useRouter()
const authStore = useAuthStore()

const login = async () => {
    try {
        const response = await api.post('/login', form.value)
        authStore.token = response.data.token
        // localStorage.setItem('auth_token', response.data.token)
        await router.push('/')
    } catch (error) {
        alert('Login failed')
        console.error(error)
    }
}


</script>
<template>
    <div class="px-[10%] py-10">
        <form @submit.prevent="login" class="flex flex-col gap-4">
            <input v-model="form.email" name="email" type="email" placeholder="Email" required />
            <input v-model="form.password" name="password" type="password" placeholder="Password" required />
            <button type="submit">Login</button>
        </form>
    </div>
</template>
