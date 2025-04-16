<script setup lang="ts">
import { useRoute } from "vue-router"
import { computed, onMounted, ref } from "vue"
import { layouts } from '@/shared/ui/layout'
import { useAuthStore } from "@/etities/auth"
import { useUsersStore } from "@/etities/user"
import Loader from "@/shared/ui/Loader.vue"

const route = useRoute()
const layout = computed(() => layouts[route.meta.layout] || layouts.default)

const authStore = useAuthStore()
const usersStore = useUsersStore()

const isAppLoading = ref(true)

onMounted(async () => {
    if (authStore.token) {
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
        <router-view v-else />
    </component>
</template>
