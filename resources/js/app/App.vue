<script setup lang="ts">
import { useRoute } from "vue-router"
import { computed, onMounted, ref } from "vue"
import { layouts } from '@/shared/ui/layout'
import Loader from "@/shared/ui/Loader.vue"
import ModalContainer from '@/widgets/modalContainer/ModalContainer.vue'
import api from "@/app/api/api"

const route = useRoute()
const layout = computed(() => layouts[route.meta.layout] || layouts.default)

// const authStore = useAuthStore()
// const usersStore = useUsersStore()

const isAppLoading = ref(false)

onMounted(async () => {
    try {
        await api.get('/auth/check')
    } catch (error) {
        localStorage.removeItem('auth_token')
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
