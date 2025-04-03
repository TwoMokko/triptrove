<script setup lang="ts">
import { ref } from "vue"
import api from "@/app/api/api"
import { useRouter } from "vue-router"
import { useAuthStore } from "@/etities/auth"
import InputCustom from "@/shared/ui/InputCustom.vue";
import ButtonCustom from "@/shared/ui/ButtonCustom.vue";
import Loader from "@/shared/ui/Loader.vue";

interface formDataType {
    login: string,
    password: string
}

const form = ref<formDataType>({
    login: '',
    password: ''
})

const router = useRouter()
const authStore = useAuthStore()

const isLoading = ref<boolean>(false)
const textBtn = ref<string>('Login')
const message = ref<string>()

const login = async () => {
    isLoading.value = true
    textBtn.value = '...'
    message.value = ''

    try {
        const response = await api.post('/login', form.value)
        authStore.token = response.data.token
        // localStorage.setItem('auth_token', response.data.token)
        await router.push('/')

        isLoading.value = false
        textBtn.value = 'Login'
    } catch (error) {
        message.value = error.response.data.message
        console.error(error)
        textBtn.value = 'Login'
    }
}

const doVerify = async () => {
    isLoading.value = true
    authStore.currentVerifyLogin = form.value.login
    const resp = await authStore.resendCode()
    console.log('doVerify update', resp)
    isLoading.value = false
    if (!resp.data) await router.push('/verify')
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
        <Loader v-if="isLoading" />
    </form>
    <div class="text-center mt-2" v-if="message">
        <div>{{ message }}</div>
        <div class="cursor-pointer text-primary" @click="doVerify">Подтвердить</div>
    </div>
</template>
