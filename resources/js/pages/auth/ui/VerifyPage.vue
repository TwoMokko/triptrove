<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from "@/entities/auth/index.js"
import { router } from "@/app/providers/router.js"
import InputCustom from "@/shared/ui/InputCustom.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
// import { useRouter } from "vue-router"

const code = ref('')
const message = ref('')
const authStore = useAuthStore()
// const router = useRouter()

const submit = async () => {
    try {
        const resp = await authStore.verifyCode(code.value)
        if (resp.status === 201) {
            message.value = ''
            await router.push('/login')
        }
    } catch (error) {
        message.value = error.response?.data?.error || 'Verification failed'
    }
}

const resend = async () => {
    try {
        const resp = await authStore.resendCode()
        message.value = resp.data?.message || 'Code resent'
    } catch (error) {
        message.value = error.response?.data?.error || 'Failed to resend code'
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="flex flex-col gap-2">
        <InputCustom v-model:value="code" :type="'text'" :placeholder="'6-digit code'" />
        <ButtonCustom :type="'submit'" :text="'Verify'"/>
        <div class="text-center cursor-pointer" @click="resend">Resend Code</div>
    </form>
    <div class="text-center mt-2" v-if="message">{{ message }}</div>
</template>
