<script setup lang="ts">
import { useRoute } from "vue-router"
import { computed, onMounted, ref, watch } from "vue"
import { layouts } from '@/shared/ui/layout'
import { useAuthStore } from "@/etities/auth"
import { useUsersStore } from "@/etities/user"
import Loader from "@/shared/ui/Loader.vue"
import ModalContainer from '@/widgets/modalContainer/ModalContainer.vue'

const route = useRoute()
const layout = computed(() => layouts[route.meta.layout] || layouts.default)

const authStore = useAuthStore()
const usersStore = useUsersStore()

const isAppLoading = ref(false)

onMounted(async () => {
    // watch(
    //     () => authStore.token,
    //     async (newToken) => {
    //         if (newToken) {
    //             isAppLoading.value = true
    //             try {
    //                 await usersStore.getUserByToken(newToken)
    //             } catch (error) {
    //                 console.error('Failed to load user:', error)
    //                 authStore.clearAuthData()
    //             } finally {
    //                 isAppLoading.value = false
    //             }
    //         }
    //     }
    // )

    if (authStore.token) {
        isAppLoading.value = true
        try {
            await usersStore.getUserByToken(authStore.token)
        } catch (error) {
            console.error('Failed to load user:', error)
            authStore.clearAuthData()
        } finally {
            isAppLoading.value = false
        }
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
