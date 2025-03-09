<script setup lang="ts">
import { useRoute } from "vue-router"
import { ref, watch } from "vue"
import { useAuthStore } from "@/etities/auth/model"
import { storeToRefs } from "pinia"

const { isAuth } = storeToRefs(useAuthStore())

const route = useRoute()
const isAuthenticated = ref(!!localStorage.getItem('auth_token'))

watch(isAuth, () => {
    isAuthenticated.value = !!localStorage.getItem("auth_token")
})
</script>

<template>
    <nav class="flex gap-4 items-center">
        <RouterLink :to="{ name: 'home' }">home</RouterLink>
        <RouterLink :to="{ name: 'profile' }" v-if="isAuthenticated">profile</RouterLink>
        <RouterLink :to="{ name: 'logout' }" v-if="isAuthenticated">logout</RouterLink>
        <RouterLink :to="{ name: 'login' }" v-if="!isAuthenticated">login</RouterLink>
        <RouterLink :to="{ name: 'register' }" v-if="!isAuthenticated">register</RouterLink>
    </nav>
</template>
