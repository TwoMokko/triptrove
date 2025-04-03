<script setup lang="ts">
import { ref } from "vue"
import { useRouter } from "vue-router"
import api from "@/app/api/api"
import InputCustom from "@/shared/ui/InputCustom.vue";
import ButtonCustom from "@/shared/ui/ButtonCustom.vue";
import Loader from "@/shared/ui/Loader.vue";

interface formDataType {
    name: string,
    email: string,
    login: string,
    password: string,
    password_confirmation: string
}

const form = ref<formDataType>({
    name: '',
    email: '',
    login: '',
    password: '',
    password_confirmation: ''
})
const router = useRouter()
const isLoading = ref<boolean>(false)
const textBtn = ref<string>('Do register')

const validate = () => {
    // validate
    auth()
}

const auth = async () => {
    // какой вариант сделать loading или менять текст
    isLoading.value = true
    textBtn.value = '...'

    try {
        const response = await api.post('/register', form.value)
        console.log('User created:', response.data)
        isLoading.value = false
        await router.push('/verify')

        isLoading.value = false
        textBtn.value = 'Do register'
    } catch (error) {
        console.error('Error creating user:', error)
    }
}
</script>

<template>
    <div class="text-center mb-4">
        <RouterLink :to="{ name: 'login' }" >Уже есть аккаунт? Авторизоваться</RouterLink>
    </div>
    <form @submit.prevent="validate" class="flex flex-col gap-4">
        <InputCustom v-model:value="form.name" :placeholder="'Name'" :type="'name'" :name="'name'" :required="true" />
        <InputCustom v-model:value="form.email" :placeholder="'Email'" :type="'email'" :name="'email'" :required="true" />
        <InputCustom v-model:value="form.login" :placeholder="'Login'" :type="'text'" :name="'login'" :required="true" />
        <InputCustom v-model:value="form.password" :placeholder="'Password'" :type="'password'" :name="'password'" :required="true" />
        <InputCustom v-model:value="form.password_confirmation" :placeholder="'Confirm Password'" :type="'password'" :name="'confirm_password'" :required="true" />
        <ButtonCustom :type="'submit'" :text="textBtn" />
        <Loader v-if="isLoading" />
    </form>
</template>
