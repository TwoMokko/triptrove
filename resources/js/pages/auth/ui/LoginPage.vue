<script setup lang="ts">
import { ref } from "vue"
import api from "@/app/api/api"
import { useRouter } from "vue-router"
import { useAuthStore } from "@/entities/auth"
import InputCustom from "@/shared/ui/InputCustom.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
import Loader from "@/shared/ui/Loader.vue"
import {useUsersStore} from "@/entities/user";


interface FormDataType {
    login: string
    password: string
}

const form = ref<FormDataType>({
    login: '',
    password: ''
})

const router = useRouter()
const authStore = useAuthStore()
const userStore = useUsersStore()

const isLoading = ref(false)
const textBtn = ref('Login')
const message = ref('')

const login = async () => {
    isLoading.value = true
    textBtn.value = '...'
    message.value = ''

    try {
        const response = await api.post('/auth/login', form.value)
        authStore.setAuthData({
            token: response.data.token,
        })

        userStore.setCurrentUser(response.data.user)
        await router.push('/')
    } catch (error) {
        message.value = error.response?.data?.message || 'Login failed'
        console.error(error)
        authStore.setAuthData({
            login: form.value.login
        })
        userStore.resetCurrentUser()
    } finally {
        isLoading.value = false
        textBtn.value = 'Login'
    }
}

const doVerify = async () => {
    isLoading.value = true
    try {
        await authStore.resendCode()
        await router.push('/verify')
    } catch (error) {
        message.value = error.response?.data?.message || 'Failed to resend code'
    } finally {
        isLoading.value = false
    }
}

</script>
<template>
    <div class="text-center mb-4">
        <RouterLink :to="{ name: 'register' }" >Нет аккаунта? Зарегистрироваться</RouterLink>
    </div>
    <form @submit.prevent="login" class="flex flex-col gap-4">
        <InputCustom v-model:value="form.login" :placeholder="'Login'" :type="'text'" :name="'login'" :required="true" />
        <InputCustom v-model:value="form.password" :placeholder="'Password'" :type="'password'" :name="'password'" :required="true" />
        <ButtonCustom :type="'submit'" :text="textBtn" />
    </form>
    <div class="text-center mt-2" v-if="message">
        <div>{{ message }}</div>
        <div v-if="message === 'Email не подтвержден'" class="cursor-pointer text-primary" @click="doVerify">Подтвердить</div>
    </div>
    <Loader v-if="isLoading" />
</template>
