<script setup lang="ts">
import { useRoute } from "vue-router"
import { computed, onMounted, ref } from "vue"
import { layouts } from '@/shared/ui/layout'
import Loader from "@/shared/ui/Loader.vue"
import ModalContainer from '@/widgets/modalContainer/ModalContainer.vue'
import api from "@/app/api/api"
import { useUsersStore } from "@/entities/user"

const route = useRoute()
const layout = computed(() => layouts[route.meta.layout] || layouts.default)

// const authStore = useAuthStore()
const usersStore = useUsersStore()

const isAppLoading = ref(true)

onMounted(async () => {
    const token = localStorage.getItem('auth_token')
    if (!token) return // Если токена нет, пропускаем проверку

    try {
        await api.get('/auth/check')
        await api.get('users/me').then(resp =>
            usersStore.setCurrentUser(resp.data.user))

    } catch (error) {
        // Удаляем токен ТОЛЬКО при 401 ошибке
        // if (error.response?.status === 401) {
        //     localStorage.removeItem('auth_token')
        // }
    } finally {
        isAppLoading.value = false
    }
})
</script>

<template>
    <component :is="layout">
        <Loader v-if="isAppLoading" />
        <RouterView v-else />
        <ModalContainer />
    </component>
</template>
