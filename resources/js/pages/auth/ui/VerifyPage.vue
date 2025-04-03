<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from "@/etities/auth/index.js"
import { router } from "@/app/providers/router.js"
import InputCustom from "@/shared/ui/InputCustom.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"

const code = ref<string>('')
const message = ref<string>()
const authStore = useAuthStore()

const submit = async () => {
    const resp = await authStore.verifyCode(code.value)
    if (resp.status == 201) {
        message.value = resp.data.error = ''
        authStore.currentVerifyLogin = ''
        await router.push('/login')
    }
    else {
        message.value = resp.data.error
    }
}

const resend = async () => {
    const resp = await authStore.resendCode()
    console.log('her', resp)
    message.value = resp.data ? resp.data.error : resp.message
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
