<script setup lang="ts">
import { ref } from "vue"
import { useRouter } from "vue-router"
import api from "@/app/api/api"

interface formDataType {
    name: string,
    email: string,
    password: string,
    password_confirmation: string
}

const form = ref<formDataType>({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})
const router = useRouter()
const isLoading = ref<boolean>(false)

const validate = () => {
    // validate
    auth()
}

const auth = async () => {
    isLoading.value = true
    try {
        const response = await api.post('/register', form.value)
        console.log('User created:', response.data)
        isLoading.value = false
        await router.push('/login')

        isLoading.value = false
    } catch (error) {
        console.error('Error creating user:', error)
    }
}
</script>

<template>
    <form @submit.prevent="validate" class="flex flex-col gap-4">
        <input v-model="form.name" name="name" type="text" placeholder="Name" required />
        <input v-model="form.email" name="email" type="email" placeholder="Email" required />
        <input v-model="form.password" name="password" type="password" placeholder="Password" required />
        <input v-model="form.password_confirmation" name="confirm_password" type="password" placeholder="Confirm Password" required />
        <button type="submit">Do register</button>
        <div v-if="isLoading">loading...</div>
    </form>
</template>
