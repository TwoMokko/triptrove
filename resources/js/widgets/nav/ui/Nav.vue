<script setup lang="ts">
import { ref, watch } from "vue"
import { storeToRefs } from "pinia"
import { useAuthStore } from "@/etities/auth"
import { useLogout } from "@/pages/auth/model/logout"

const { isAuth } = storeToRefs(useAuthStore())
const isAuthenticated = ref(!!localStorage.getItem('auth_token'))
const { isLoadingLogout, doLogout } = useLogout()

watch(isAuth, () => {
    isAuthenticated.value = !!localStorage.getItem("auth_token")
})
</script>

<template>
    <nav class="flex gap-4 items-center">
        <RouterLink :to="{ name: 'home' }">home</RouterLink>
        <RouterLink :to="{ name: 'login' }" v-if="!isAuthenticated">login</RouterLink>
        <RouterLink :to="{ name: 'register' }" v-if="!isAuthenticated">register</RouterLink>
        <RouterLink :to="{ name: 'travels' }" v-if="isAuthenticated">my travels</RouterLink>
        <RouterLink :to="{ name: 'profile' }" v-if="isAuthenticated">profile</RouterLink>
        <a
            v-if="isAuthenticated"
            @click="doLogout"
            class="cursor-pointer"
            :class="isLoadingLogout ? 'text-gray-400' : ''"
        >
            logout
        </a>
    </nav>
</template>
