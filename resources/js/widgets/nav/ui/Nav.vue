<script setup lang="ts">
import { computed } from "vue"
import { useLogout } from "@/processes/auth/lib/use-logout";
import { useAuthStore } from "@/processes/auth/model/store";

const { isLoadingLogout, doLogout } = useLogout();
const authStore = useAuthStore();
const isAuthenticated = computed(() => authStore.isAuthenticated);

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
