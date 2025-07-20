<script setup lang="ts">
import { useRoute, useRouter } from "vue-router"
import {computed, onMounted, ref, watch} from "vue"
import { layouts } from '@/shared/ui/layout'
import Loader from "@/shared/ui/Loader.vue"
import ModalContainer from '@/widgets/modalContainer/ModalContainer.vue'
import api from "@/app/api/api"
import { useUsersStore } from "@/entities/user"

const route = useRoute()
const router = useRouter()
const layout = computed(() => layouts[route.meta.layout] || layouts.default)

const usersStore = useUsersStore()
const isAppLoading = ref(true)

const checkAuthAndLoadData = async () => {
    if (!route.meta.requiresAuth) {
        isAppLoading.value = false
        return
    }

    const token = localStorage.getItem('auth_token')
    if (!token) {
        isAppLoading.value = false
        return
    }

    try {
        await api.get('/auth/check')
        const userResponse = await api.get('users/me')
        usersStore.setCurrentUser(userResponse.data.user)
    } catch (error) {
        localStorage.removeItem('auth_token')
        await router.push('/login')
    } finally {
        isAppLoading.value = false
    }
}

onMounted(checkAuthAndLoadData)

watch(
    () => route.path,
    () => {
        if (route.meta.requiresAuth) {
            checkAuthAndLoadData()
        }
    }
)




// const checkAuth = async () => {
//     if (!route.meta.requiresAuth) return
//
//     const token = localStorage.getItem('auth_token')
//     if (!token) {
//         await router.push('/login')
//         return;
//     }
//
//     try {
//         await api.get('/auth/check')
//     } catch {
//         localStorage.removeItem('auth_token')
//         await router.push('/login')
//     }
// }
//
// onMounted(checkAuth)
//
// watch(
//     () => route.path,
//     () => {
//         if (route.meta.requiresAuth) {
//             checkAuth()
//         }
//     }
// )
</script>

<template>
    <component :is="layout">
        <Loader v-if="isAppLoading" />
        <RouterView v-else :key="route.fullPath" />
        <ModalContainer />
    </component>
</template>
