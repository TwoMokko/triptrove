<script setup lang="ts">
import { ref } from "vue"
import api from "@/app/api/api"
import { useRouter } from "vue-router"
import { useAuthStore } from "@/etities/auth"
import InputCustom from "@/shared/ui/InputCustom.vue";
import ButtonCustom from "@/shared/ui/ButtonCustom.vue";
import Loader from "@/shared/ui/Loader.vue";

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

const isLoading = ref<boolean>(false)
const textBtn = ref<string>('Login')

const login = async () => {
    isLoading.value = true
    textBtn.value = '...'

    try {
        const response = await api.post('/login', form.value)
        authStore.token = response.data.token
        // localStorage.setItem('auth_token', response.data.token)
        await router.push('/')

        isLoading.value = false
        textBtn.value = 'Login'
    } catch (error) {
        alert('Login failed')
        console.error(error)
        textBtn.value = 'Login'
    }
}


</script>
<template>
    <div class="text-center mb-4">
        <RouterLink :to="{ name: 'register' }" >Нет аккаунта? Зарегистрироваться</RouterLink>
    </div>
    <form @submit.prevent="login" class="flex flex-col gap-4">
        <InputCustom v-model:value="form.email" :placeholder="'Email'" :type="'email'" :name="'email'" :required="true" />
        <InputCustom v-model:value="form.password" :placeholder="'Password'" :type="'password'" :name="'password'" :required="true" />
        <ButtonCustom :type="'submit'" :text="textBtn" />
        <Loader v-if="isLoading" />
    </form>
</template>
