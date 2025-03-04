<script setup lang="ts">
import Logo from "@/shared/Logo.vue";
</script>

<template>
    <header class="px-[10%] py-4 flex gap-4 items-center justify-between">
        <RouterLink to="/">
            <Logo />
        </RouterLink>
        <nav class="flex gap-4 items-center">
            <RouterLink to="/">home</RouterLink>
            <RouterLink to="/profile">profile</RouterLink>
        </nav>
    </header>
    <main>
        <RouterView />
    </main>
</template>
