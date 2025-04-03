<script setup>
import { ref } from 'vue'
import { useAuthStore } from "@/etities/auth/index.js"
import { router } from "@/app/providers/router.js"
import InputCustom from "@/shared/ui/InputCustom.vue";
import ButtonCustom from "@/shared/ui/ButtonCustom.vue";

const code = ref('');
const authStore = useAuthStore();

const submit = async () => {
    try {
        const resp = await authStore.verifyCode(code.value)

        switch (resp) {
            case 400: console.log('error 400'); break;
            case 401: console.log('error 401'); break;
            case 404: console.log('error 404'); break;
            case 429: console.log('error 429'); break;
            default: await router.push('/login'); break;
        }

    } catch (error) {
        alert(error.response.data.error)
    }
}

const resend = async () => {
    await authStore.resendCode()
}
</script>

<template>
    <form @submit.prevent="submit" class="flex flex-col gap-2">
        <InputCustom v-model:value="code" :type="'text'" :placeholder="'6-digit code'" />
        <ButtonCustom :type="'submit'" :text="'Verify'"/>
        <button @click="resend">Resend Code</button>
    </form>
</template>
