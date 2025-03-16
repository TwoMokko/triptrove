<script setup lang="ts">
import { computed, ComputedRef, onMounted } from 'vue'
import Loader from "@/shared/ui/Loader.vue"
import { useUsersStore } from "@/etities/user"
import { useAuthStore } from "@/etities/auth"

const authStore = useAuthStore()
const usersStore = useUsersStore()

const isLoading: ComputedRef<boolean> = computed(() => {
    return usersStore.currentUser === null
})

const fetchUser = async () => {
    await usersStore.getUserByToken(authStore.token)
}

onMounted(() => {
    fetchUser()
})
</script>

<template>
    <div  class="px-[10%] py-10">
        <Loader v-if="isLoading" />
        <div v-else>
            <h1 class="text-2xl mb-4">Имя: {{ usersStore.currentUser.name }}</h1>
            <div>id: {{ usersStore.currentUser.id }}</div>
            <div>email: {{ usersStore.currentUser.email }}</div>
        </div>
    </div>
</template>

